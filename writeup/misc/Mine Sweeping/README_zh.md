## Mine Sweeping

>源unity工程`Mine Sweeping Unity`
>
>附件`Mine Sweeping Exe\Mine Sweeping.exe`

### 分析

Elements.cs
```C#
class Elements: MonoBehaviour
{
    void Awake()
    {   
        int x = (int)transform.position.x;
        int y = (int)transform.position.y;
        //根据全局的数组设置该格子是雷还是空地
        bIsMine = (((MayWorldBeAtPeace[x, y] ^ AreYouFerryMen[x, y]) - 233) / 2333) == 1 ? true : false;
        //根据格子的position，将物体实例绑定到网格中
        Grids._instance.eleGrids[(int)transform.position.x, (int)transform.position.y] = this;
        //网格中对应格子数值设置
        Grids._instance.DevilsInHeaven[(int)transform.position.x, (int)transform.position.y] = (bIsMine == true ? 1 : 0);
        //隐藏reset按钮
        resetButton = GameObject.FindGameObjectWithTag("resetButton");
        if (resetButton)
            resetButton.SetActive(false);
    }

    // Start is called before the first frame update
    void Start()
    {
        //初始化时混淆地图
        Grids._instance.ChangeMap();
        //测试用
        //DawnsLight();
    }
    ...
    void OnMouseUpAsButton()
    {
        //鼠标点击对应格子触发
        if (!Grids._instance.bGameEnd && !bIsOpen)
        {   //未翻开
            //设置翻开
            bIsOpen = true;
            int nX = (int)transform.position.x;
            int nY = (int)transform.position.y;
            if (bIsMine)
            {
                //显示雷
                SafeAndThunder(0);
                Grids._instance.bGameEnd = true;
                //游戏失败
                Grids._instance.GameLose();
                print("game over: lose");
            }
            else
            {
                //翻到的不是雷，显示周围雷的数量+翻开相邻的周围无雷的格子
                int adjcentNum = Grids._instance.CountAdjcentNum(nX, nY);
                SafeAndThunder(adjcentNum);
                Grids._instance.Flush(nX, nY, new bool[Grids.w, Grids.h]);
            }
            if (Grids._instance.GameWin())
            {
                //游戏胜利
                Grids._instance.bGameEnd = true;
                print("game over: win");
            }
        }
    }
}
```
Elements.cs是挂在每个格子身上的脚本，Awake中确定该格子是雷还是空地，Start中将地图中固定的六个摇摆位随机化，OnMouseUpAsButton检测当前格子是不是雷，并作出相应处理


Grid.cs
```C#
    public bool GameWin()
    {
        foreach (Elements ele in eleGrids)
        {
            if (!ele.bIsOpen && !ele.bIsMine)
            {   //存在没翻开且不是雷的
                return false;
            }
        }
        foreach (Elements ele in eleGrids)
        {   //加载最后的图片
            ele.DawnsLight();
        }
        return true;
    }

    public void ChangeMap()
    {
        System.Random ran = new System.Random((int)System.DateTime.Now.Millisecond);
        const int SwingNum = 6;
        const int Start = 0;
        const int End = 100;
        int[] SwingPosX = new int[SwingNum]{ 9, 15, 21, 10, 18, 12, };
        int[] SwingPosY = new int[SwingNum]{ 0, 7, 15, 3, 16, 28 };
        int[] RandomNum = new int[SwingNum];
        for (int i = 0; i < SwingNum; i++)
        {
            RandomNum[i] = ran.Next(Start, End);
        }

        for (int i = 0; i < SwingNum; i++)
        {
            int x = SwingPosX[i];
            int y = SwingPosY[i];
            eleGrids[x, y].bIsMine = RandomNum[i] > 60 ? false : true ;
            DevilsInHeaven[x, y] = eleGrids[x, y].bIsMine == true ? 1 : 0;
        }
    }
```
Grid.cs是控制网格的脚本，主要就是检测游戏输赢以及是否按下reset按钮，ChangeMap函数会将六个摇摆位的01随机化，起到混淆作用

### exp
1. 直接做，每次点到雷了，就记录雷的位置，反正reset按钮只会将格子都翻面，不会改变格子的01值，保守估计30min可以解决
2. 逆向，分析Elements.cs，得知每个格子是不是雷，是通过全局数组决定的，然后拿全局数组MayWorldBeAtPeace和AreYouFerryMen做对应处理就可以了
3. 动态调试，在游戏进去后查看Grid.cs中的，用来保存游戏数据以便reset按钮执行的DevilsInHeaven数组，解决
4. 改代码，通过底层修改Grid.cs中检测游戏输赢的if语句，直接加载最后的二维码