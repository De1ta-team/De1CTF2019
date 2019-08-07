(function () {

    var Sprite = Laya.Sprite;

    function BgManager() {
        var _this = this;
        BgManager.super(_this);

        _this.initBg();
    }

    Laya.class(BgManager, "BgManager", Sprite);

    var _proto = BgManager.prototype;
    _proto.initBg = function () {
        var bgContainer = new Sprite();
        this.addChild(bgContainer);
        bgContainer.loadImage("res/imgs/bg.png");
    }

})();