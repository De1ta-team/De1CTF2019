(function () {
    var Stage = Laya.Stage;
    var Handler = Laya.Handler;
    var WebGL = Laya.WebGL;
    var Loader = Laya.Loader;
    var Stat = Laya.Stat;
    var Sprite = Laya.Sprite;
    var Event = Laya.Event;

    (function () {
//        Laya.init(Browser.clientWidth, Browser.clientHeight, WebGL);
//        Laya.init(Browser.clientWidth, Browser.clientHeight);
        Laya.init(1080, 1920);

        Laya.stage.alignV = Stage.ALIGN_MIDDLE;
        Laya.stage.alignH = Stage.ALIGN_CENTER;

        Laya.stage.screenMode = Stage.SCREEN_NONE;
        Laya.stage.bgColor = "#232628";
//        Stat.show();
        init();
    })();

    function init() {
        var res = [];
        res.push({url: "res/mc/assets.json", type: Loader.ATLAS});
        res.push({url: "res/mc/assets.png", type: Loader.IMAGE});
        Laya.loader.load(res, Handler.create(this, onComplete));
    }

    function onComplete() {
        var gameManager = new GameManager();
        Laya.stage.addChild(gameManager);
    }

})();
