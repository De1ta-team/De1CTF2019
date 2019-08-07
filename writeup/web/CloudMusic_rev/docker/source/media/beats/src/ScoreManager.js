(function () {

    var Sprite = Laya.Sprite;

    var scorePanel;//分数区容器

    function ScoreManager() {
        var _this = this;
        ScoreManager.super(_this);

        _this.initBg();
    }

    Laya.class(ScoreManager, "ScoreManager", Sprite);

    var _proto = ScoreManager.prototype;
    _proto.initBg = function () {
        var _this = this;
        scorePanel = new Sprite();
        scorePanel.loadImage("res/imgs/BG_01.png");
        _this.addChild(scorePanel);
    }

})();