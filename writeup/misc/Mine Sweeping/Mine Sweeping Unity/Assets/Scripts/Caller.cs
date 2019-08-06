using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Caller : MonoBehaviour
{
    public GameObject resetButton;

    // Start is called before the first frame update
    void Start()
    {
        
    }

    // Update is called once per frame
    void Update()
    {
        
    }

    public void CallResetMap()
    {
        Grids._instance.ResetMap();
        resetButton = GameObject.FindGameObjectWithTag("resetButton");
        resetButton.SetActive(false);
    }
}
