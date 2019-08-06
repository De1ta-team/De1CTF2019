import numpy as np
from keras.callbacks import EarlyStopping
from keras.models import Model
from keras.layers import Dense, Input


def enc_model(flag_shape, enc_shape):
    inp = Input((flag_shape,))
    out = Dense(enc_shape)(inp)
    return Model(inp, out)

def load_data(flag_name, enc_name):
    '''

    :param path: data path
    :return:
        flag_sample: shape=(512,128)
        enc_sample:shape=(512,64)
    '''
    flag_sample = np.loadtxt(flag_name)
    enc_sample = np.loadtxt(enc_name)
    return flag_sample, enc_sample

def train_enc(flag_sample, enc_sample):
    flag_shape = flag_sample.shape[-1]
    enc_shape = enc_sample.shape[-1]
    Enc_model = enc_model(flag_shape, enc_shape)
    Enc_model.compile(loss='mean_absolute_error', optimizer='Adam')
    print(Enc_model.summary())
    ear = EarlyStopping(monitor='loss', patience=10, mode='min', restore_best_weights=True)
    print("Train Enc_model")
    Enc_model.fit(flag_sample, enc_sample, batch_size=512, epochs=100000000, verbose=2, validation_split=0.1,
                  callbacks=[ear])
    Enc_model.save("enc.hdf5")




if __name__ == '__main__':
    flag_sample, enc_sample = load_data('flag_sample.txt', 'enc_sample.txt')
    train_enc(flag_sample, enc_sample)
