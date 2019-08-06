using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Grids : MonoBehaviour
{
    public static Grids _instance;

    public const int w = 29;

    public const int h = 29;

    public Elements[,] eleGrids = new Elements[w, h];

    public bool bGameEnd = false;

    public GameObject resetButton;
    
    public GameObject alleles;

    private void Start()
    {
        _instance = this;
        alleles.SetActive(true);
    }

    public int[,] DevilsInHeaven = new int[29, 29]
    {
        
        {1,0,0,0,0,0,1,0,0,1,1,0,0,1,0,0,0,0,0,1,1,0,1,0,0,0,0,0,1 },
        {1,1,1,1,1,1,1,0,1,0,1,0,1,1,1,0,0,0,0,0,1,0,1,1,1,1,1,1,1 },
        {1,0,1,1,1,0,1,0,0,1,1,1,0,0,1,0,0,0,1,0,1,0,1,0,1,1,1,0,1 },
        {0,0,0,0,0,0,0,0,0,1,0,1,1,0,0,1,0,0,0,1,0,0,0,0,0,0,0,0,0 },
        {0,0,1,0,1,1,1,0,1,1,0,1,0,1,1,0,0,0,0,0,1,1,0,0,0,1,0,0,1 },
        {0,0,0,1,1,0,0,0,0,1,1,1,1,0,0,0,0,1,0,0,0,1,1,1,0,0,0,1,1 },
        {1,0,1,1,1,0,1,0,1,1,0,0,1,1,0,0,1,0,0,1,1,0,1,0,1,1,1,0,1 },
        {1,0,1,1,1,0,1,0,0,0,0,1,0,1,0,0,1,1,1,1,1,0,1,0,1,1,1,0,1 },
        {1,1,1,1,1,1,1,0,1,1,1,0,1,0,1,0,1,0,1,0,1,0,1,1,1,1,1,1,1 },
        {1,0,0,0,0,0,1,0,0,1,1,0,0,0,1,1,0,0,1,1,0,0,1,0,0,0,0,0,1 },
        {0,1,1,0,1,0,1,0,1,1,1,1,1,0,0,1,0,0,1,0,1,1,1,0,0,0,1,1,1 },
        {0,0,0,1,0,1,0,0,0,1,0,1,1,1,0,0,0,0,0,0,0,0,1,1,1,0,0,0,1 },
        {1,0,0,0,0,0,1,0,1,0,0,0,0,1,1,0,1,0,1,0,1,0,0,0,1,0,0,1,1 },
        {0,0,1,0,0,0,0,0,1,1,0,0,0,0,0,1,0,1,0,1,1,0,1,1,0,0,1,0,1 },
        {0,0,1,1,1,1,0,1,0,1,0,0,1,0,1,1,0,1,1,0,1,0,1,1,1,1,0,1,0 },
        {1,1,1,1,0,0,1,0,1,0,1,1,1,0,0,1,1,0,0,1,0,0,1,0,0,0,0,0,0 },
        {1,0,1,1,1,0,1,0,1,1,0,0,1,1,1,0,0,0,0,0,1,1,1,1,1,1,0,1,0 },
        {1,0,1,1,1,0,1,0,0,0,0,1,0,1,1,0,1,1,1,0,0,0,0,0,1,1,0,0,0 },
        {1,0,1,1,1,0,1,0,1,1,0,1,1,0,0,0,1,0,0,0,1,1,0,0,1,0,0,0,1 },
        {0,1,1,0,1,0,1,1,0,1,1,0,0,0,1,1,0,1,1,0,1,1,1,0,1,1,0,0,1 },
        {0,0,1,1,0,1,0,0,0,0,0,1,0,0,1,0,1,1,0,1,0,1,1,0,0,0,1,1,1 },
        {1,0,0,0,1,1,1,1,0,0,1,0,0,0,0,1,1,0,0,0,1,1,1,1,1,0,1,1,1 },
        {1,1,0,1,0,1,1,0,0,1,0,0,0,1,1,0,1,1,1,1,1,1,0,0,0,0,0,1,1 },
        {1,0,1,1,1,1,1,0,0,0,0,1,1,0,0,1,0,0,0,1,1,0,1,0,1,0,0,1,1 },
        {1,0,0,1,1,0,1,1,0,1,1,1,0,1,0,0,0,1,0,0,0,1,0,1,1,1,0,1,1 },
        {0,1,0,0,0,1,0,0,1,0,1,0,1,0,0,1,1,0,0,1,1,0,1,0,0,0,0,0,0 },
        {0,0,0,0,0,0,0,0,1,1,1,1,0,1,1,0,1,0,0,0,1,0,0,0,1,0,0,1,1 },
        {1,1,1,1,1,1,1,0,0,1,0,0,1,1,1,0,1,1,1,1,1,0,1,0,1,0,1,1,1 },
        {1,0,0,0,0,0,1,0,0,1,0,1,1,1,0,0,0,1,1,0,0,0,0,1,0,0,0,1,0 }
    };

    public int CountAdjcentNum(int x, int y)
    {
        int count = 0;
        int[] raw = new int[] { -1, 0, 1 };
        int[] col = new int[] { -1, 0, 1 };
        for (int i = 0; i < 3; i++)
        {
            for (int j = 0; j < 3; j++)
            {
                if (MineAt(x + raw[i], y + col[j]))
                {
                    count++;
                }
            }
        }
        return count;
    }

    public bool MineAt(int nX, int nY)
    {
        return (0 <= nX && nX < w && 0 <= nY && nY < h && eleGrids[nX, nY].bIsMine);
    }

    public void Flush(int nX, int nY, bool[,] visited)
    {
        if (0 <= nX && nX < w && 0 <= nY && nY < h)
        {
            if (visited[nX, nY])
                return;
            visited[nX, nY] = true;
            //翻开
            eleGrids[nX, nY].bIsOpen = true;
            eleGrids[nX, nY].SafeAndThunder(CountAdjcentNum(nX, nY));

            if (CountAdjcentNum(nX, nY) > 0)
                return;

            Flush(nX - 1, nY, visited);
            Flush(nX + 1, nY, visited);
            Flush(nX, nY - 1, visited);
            Flush(nX, nY + 1, visited);
        }
    }

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

    public void GameLose()
    {
        resetButton.SetActive(true);
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

    public void ResetMap()
    {
        bGameEnd = false;
        for (int i = 0; i < w; i++)
        {
            for (int j = 0; j < h; j++)
            {
                eleGrids[i, j].bIsOpen = false;
                eleGrids[i, j].bIsMine = DevilsInHeaven[i, j] == 1 ? true : false;
                eleGrids[i, j].LayersOfFear();
            }
        }
    }
}
