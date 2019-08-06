#!/usr/bin/python3
# -*-coding:utf-8 -*-
# Reference:**********************************************
# @Time     : 2019/7/6 15:05
# @Author   : Raymond Luo
# @File     : train.py
# @User     : luoli
# @Software: PyCharm
# Reference:**********************************************
import numpy as np
from keras.models import Model, load_model
from keras.callbacks import EarlyStopping
from keras.models import Model
from keras.layers import Dense, Input
from judge import judge

def enc_model(flag_shape, enc_shape):
    inp = Input((flag_shape,))
    out = Dense(enc_shape)(inp)
    return Model(inp, out)


def dec_model(enc_shape, flag_shape):
    inp = Input((enc_shape,))
    h = Dense(4096)(inp)
    h = Dense(4096)(h)
    out = Dense(flag_shape, activation='sigmoid')(h)
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


def train(flag_sample, enc_sample):
    flag = flag_sample[256].reshape((1, -1))
    flag_shape = flag_sample.shape[-1]
    enc_shape = enc_sample.shape[-1]
    Enc_model = enc_model(flag_shape, enc_shape)
    Dec_model = dec_model(enc_shape, flag_shape)
    Enc_model.compile(loss='mean_absolute_error', optimizer='Adam')
    print(Enc_model.summary())
    print("Train Enc_model")
    Enc_model.fit(flag_sample, enc_sample, batch_size=512, epochs=5000, verbose=0)
    flag_enc = Enc_model.predict(flag)
    print("Train Dec_model")
    Enc_model.trainable = False
    inp = Enc_model.inputs
    dec = Enc_model(inp)
    out = Dec_model(dec)
    model = Model(inp, out)
    model.compile(loss='mean_absolute_error', optimizer='Adam')
    print(model.summary())
    model.fit(flag_sample, flag_sample, batch_size=512, epochs=5000, verbose=0)
    Dec_flag = np.round(Dec_model.predict(flag_enc))
    print("flag: {}".format(flag))
    # print("enc flag:{}".format(flag_enc))
    print("dec flag:{}".format(Dec_flag))
    loss = mean_absolute_error(flag, Dec_flag)
    print("dec loss:{}".format(loss))


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
    Enc_model.save("../model/enc.hdf5")


def gen_enc_flag():
    Enc_model = load_model("../model/enc.hdf5")
    flag = np.random.randint(0, 2, size=(1, 128))
    flag_enc = Enc_model.predict(flag)
    np.savetxt("../data/flag.txt", flag, fmt="%d")
    np.savetxt("../data/flag_enc.txt", flag_enc)


def train_dec(flag_sample, enc_sample):
    flag_shape = flag_sample.shape[-1]
    enc_shape = enc_sample.shape[-1]
    Enc_model = load_model("enc.hdf5")
    Enc_model.name = "Enc"
    Dec_model = dec_model(enc_shape, flag_shape)
    Dec_model.name = 'Dec'
    print("Train Dec_model")
    Enc_model.trainable = False
    inp = Enc_model.inputs
    dec = Enc_model(inp)
    out = Dec_model(dec)
    model = Model(inp, out)
    model.compile(loss='mean_absolute_error', optimizer='Adam')
    print(model.summary())
    ear = EarlyStopping(monitor='val_loss', patience=10, mode='min', restore_best_weights=True)
    model.fit(flag_sample, flag_sample, batch_size=512, epochs=100000000, verbose=2, shuffle=True,
              validation_split=0.05,
              callbacks=[ear])
    print(Dec_model.summary())
    Dec_model.save("dec.hdf5")


def solve():
    Dec_model = load_model("dec.hdf5")
    flag_enc = np.loadtxt("flag_enc.txt").reshape(1, -1)
    flag_dec = Dec_model.predict(flag_enc)
    np.savetxt("flag_dec.txt", flag_dec)
    # print(flag_dec[0])
    judge(flag_dec)

if __name__ == '__main__':
    flag_sample, enc_sample = load_data('flag_sample.txt', 'enc_sample.txt')
    # train(flag_sample, enc_sample)
    # train_enc(flag_sample, enc_sample)
    # gen_enc_flag()
    train_dec(flag_sample, enc_sample)
    solve()
