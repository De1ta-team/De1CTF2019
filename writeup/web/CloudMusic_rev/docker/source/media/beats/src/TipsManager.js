(function () {

    var Event = Laya.Event;
    var Handler = Laya.Handler;
    var Sprite = Laya.Sprite;
    var TimeLine = Laya.TimeLine;
    var Text = Laya.Text;
    var Tween = Laya.Tween;

    var readyTip;
    var goTip;
    var timesup;

    var fantasticTip;
    var perfectTip;
    var goodTip;
    var missTip;
    var comboTip;
    var comboTxt;//连击文本
    var countDownTxt;//倒计时文本
    var scoreTxt;//分数文本

    var lastTip;

    var comboCount = 0;
    var countDown = 35;

    var scoreObj = {
        fantastic: 0,
        perfect: 0,
        good: 0,
        miss: 0,
        comboMax: 0,
        totalScore: 0
    };

    function TipsManager() {
        var _this = this;
        TipsManager.super(_this);

        _this.initTips();
    }

    Laya.class(TipsManager, "TipsManager", Sprite);

    var _proto = TipsManager.prototype;

    _proto.initTips = function () {
        var _this = this;

        readyTip = new Sprite();
        readyTip.loadImage("res/imgs/ready.png");
        readyTip.alpha = 0;
        readyTip.pivot(384, 172);
        readyTip.pos((Laya.stage.width) / 2, 820);
        readyTip.scaleX = 0;
        readyTip.scaleY = 0;
        _this.addChild(readyTip);

        goTip = new Sprite();
        goTip.loadImage("res/imgs/go.png");
        goTip.alpha = 0;
        goTip.pivot(436, 172);
        goTip.pos((Laya.stage.width) / 2, 820);
        goTip.scaleX = 0;
        goTip.scaleY = 0;
        _this.addChild(goTip);

        timesup = new Sprite();
        timesup.loadImage("res/imgs/timesup.png");
        timesup.alpha = 0;
        timesup.pivot(378, 84);
        timesup.pos((Laya.stage.width) / 2, 820);
        _this.addChild(timesup);

        fantasticTip = new Sprite();
        fantasticTip.loadImage("res/imgs/fantastic.png");
        fantasticTip.alpha = 0;
        fantasticTip.pivot(221, 66);
        fantasticTip.pos((Laya.stage.width) / 2 - 35, 820);
        _this.addChild(fantasticTip);

        perfectTip = new Sprite();
        perfectTip.loadImage("res/imgs/perfect.png");
        perfectTip.alpha = 0;
        perfectTip.pivot(221, 66);
        perfectTip.pos((Laya.stage.width) / 2, 820);
        _this.addChild(perfectTip);

        goodTip = new Sprite();
        goodTip.loadImage("res/imgs/good.png");
        goodTip.alpha = 0;
        goodTip.pivot(172, 66);
        goodTip.pos((Laya.stage.width) / 2, 820);
        _this.addChild(goodTip);

        missTip = new Sprite();
        missTip.loadImage("res/imgs/miss.png");
        missTip.alpha = 0;
        missTip.pivot(163, 66);
        missTip.pos((Laya.stage.width) / 2, 820);
        _this.addChild(missTip);

        comboTip = new Sprite();
        comboTip.loadImage("res/imgs/combo.png");
        comboTip.alpha = 0;
        comboTip.pivot(183, 66);
        comboTip.pos((Laya.stage.width) / 2 - 60, 600);

        comboTxt = new Text();
        comboTxt.font = "Impact";
        comboTxt.fontSize = 130;
        comboTxt.color = "#FFE202";
        comboTxt.x = 370;
        comboTxt.y = -20;
        comboTxt.text = '';

        comboTip.addChild(comboTxt);
        _this.addChild(comboTip);

        countDownTxt = new Text();
        countDownTxt.font = "Impact";
        countDownTxt.fontSize = 50;
        countDownTxt.color = "#21D4A1";
        countDownTxt.x = 230;
        countDownTxt.y = 152;
        countDownTxt.width = 50;
        countDownTxt.text = countDown.toString();
        _this.addChild(countDownTxt);

        scoreObj.totalScore = 0;
        scoreTxt = new Text();
        scoreTxt.font = "Impact";
        scoreTxt.fontSize = 50;
        scoreTxt.color = "#21D4A1";
        scoreTxt.x = 794;
        scoreTxt.y = 184;
        scoreTxt.width = 80;
        scoreTxt.align = 'center';
        scoreTxt.text = scoreObj.totalScore.toString();
        _this.addChild(scoreTxt);
    }

    _proto.setCountDown = function () {
        var _this = this;
        Laya.timer.loop(1000, _this, countDownHandler);

        function countDownHandler() {
            countDown--;
            countDownTxt.text = countDown.toString();
            if (countDown == 0) {
                Laya.timer.clear(_this, countDownHandler);
                _this.event("End_Game_Event");
            }
        }
    }

    _proto.setScore = function (addScore) {
        scoreObj.totalScore += addScore;
        scoreTxt.text = scoreObj.totalScore.toString();
    }
    
    _proto.getScore = function() {
        return scoreObj;
    }

    _proto.countComboTotalScore = function () {
        if(scoreObj.comboMax > 0) {
            scoreObj.comboMax--;
        }
        scoreObj.totalScore += scoreObj.comboMax * 10;
        scoreTxt.text = scoreObj.totalScore.toString();
    }

    _proto.showCombo = function (num) {
        var handler = new Handler(lastTip, function () {
            Tween.to(comboTip, {alpha: 0, scaleX: 1.5, scaleY: 1.5, y: 600}, 100, null, null, 250);
        });

        comboTip.scaleX = 0.2;
        comboTip.scaleY = 0.2;
        comboTip.y = 650;
        comboTip.alpha = 0;

        if (num != 1) {
            comboTip.pos((Laya.stage.width) / 2 - 60, 650);
            comboTxt.text = ' ' + num;
        } else {
            comboTip.pos((Laya.stage.width) / 2, 650);
            comboTxt.text = '';
        }

        Tween.to(comboTip, {alpha: 1, scaleX: 0.8, scaleY: 0.8}, 50, null, handler, 120);
    }

    _proto.showPlayTip = function (addScore) {
        var _this = this;
        if (addScore == 20) {
            scoreObj.fantastic++;
            comboCount++;
            if(comboCount > scoreObj.comboMax) {
                scoreObj.comboMax = comboCount;
            }
            _this.showTip(fantasticTip);
        } else if (addScore == 10) {
            scoreObj.perfect++;
            comboCount++;
            if(comboCount > scoreObj.comboMax) {
                scoreObj.comboMax = comboCount;
            }
            _this.showTip(perfectTip);
        } else if (addScore == 5) {
            scoreObj.good++;
            comboCount++;
            if(comboCount > scoreObj.comboMax) {
                scoreObj.comboMax = comboCount;
            }
            _this.showTip(goodTip);
        } else if (addScore == 0) {
            scoreObj.miss++;
            comboCount = 0;
            _this.showTip(missTip);
        }
        _this.setScore(addScore);
    }

    _proto.showTip = function (newTip) {
        var _this = this;

        if (lastTip) {
            Tween.clearAll(lastTip)
            lastTip.alpha = 0;
            lastTip.scaleX = 0.4;
            lastTip.scaleY = 0.4;
        }

        var handler = new Handler(lastTip, function () {
            Tween.to(newTip, {alpha: 0, y: 790}, 100, null, null, 250);
        });

        newTip.scaleX = 0.4;
        newTip.scaleY = 0.4;
        newTip.y = 820;
        newTip.alpha = 0;
        Tween.to(newTip, {alpha: 1, scaleX: 1, scaleY: 1}, 100, null, handler);
        lastTip = newTip;

        if (comboCount >= 2) {
            _this.showCombo(comboCount - 1);
        }
    }

    _proto.readyGO = function () {
        var _this = this;

        var timeLine = new TimeLine();
        timeLine.addLabel("readyIn", 0).to(readyTip, {scaleX: 1, scaleY: 1, alpha: 1}, 500, null, 0)
            .addLabel("readyOut", 0).to(readyTip, {scaleX: 5, scaleY: 5, alpha: 0}, 200, null, 0)
            .addLabel("goIn", 0).to(goTip, {scaleX: 1, scaleY: 1, alpha: 1}, 500, null, 0)
            .addLabel("goOut", 0).to(goTip, {alpha: 0}, 500, null, 0);
        timeLine.play(0, false);
        timeLine.on(Event.LABEL, this, onLabel);
        timeLine.on(Event.COMPLETE, this, onComplete);

        function onLabel(label) {
            if (label == "readyOut") {
                timeLine.pause();
                setTimeout(function () {
                    timeLine.resume();
                }, 500);
            }
        }

        function onComplete() {
            timeLine.destroy();
            goTip.alpha = 0;
            _this.setCountDown();
            _this.event("Start_Game_Event");
        }
    }
})();