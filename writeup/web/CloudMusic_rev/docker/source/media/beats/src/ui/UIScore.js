(function () {
    var Handler = Laya.Handler;
    var Sprite = Laya.Sprite;
    var Loader = Laya.Loader;

    var liziCon;

    function UIScore(letter, width) {
        UIScore.super(this);

        letter = letter;

        liziCon = new Sprite();
        this.addChild(liziCon);
    }

    Laya.class(UIScore, "UIScore", Sprite);

    var _proto = UIScore.prototype;

})();
