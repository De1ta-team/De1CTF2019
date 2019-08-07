let usrName = 'de1ta'
let nowPosition = '/sandbox'
let commandList = 'cd ls cat hey hi hello help clear exit ~ / ./'.split(' ')
let hisCommand = []
let cour = 0
let isInHis = 0
let directory = []
let files = []

let host = ''

let e_console = $('#console')
let e_main = $('#main')
let e_input = $('.input-text')
let e_html = $('body,html')
let e_pos = $('#pos')

/*
[Developer Notes]
OTP Library for Python located in js/pyotp.zip
Server Params:
digits = 8
interval = 5
window = 1
*/

let mainFunc = (input, position) => {
  if (input === '') {
    e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + '<br/>')
    if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
  } else {
    command = input.split(' ')[0]
    if (commandList.indexOf(command) === -1) {
      $.ajax({
        url: host + '/shell.php?a='+encodeURIComponent(input)+'&totp=' + new TOTP("GAXG24JTMZXGKZBU",8).genOTP(),
        type: "GET",
        dataType: 'json',
        success: (res) => {
            e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>' + res.message + '<br/>')
            if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
        },
        error: (res) => {
            e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>System Fatal Error!<br/>')
            if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
        }
      })
    } else {
      switch (command) {
        case 'help':
          e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>' + 'command [Options...]<br/>You can use following commands:<br/><br/>cd<br/>ls<br/>cat<br/>clear<br/>help<br/>exit<br/><br/>Besides, there are some hidden commands, try to find them!<br/>')
          if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
          break
        case 'exit':
          e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>(๑˘̴͈́꒵˘̴͈̀)۶ˮ вyё вyё~<br/>')
          if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
          location.reload()
          break
        case 'hi':
        case 'hey':
        case 'hello':
          e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>Nice to Meet U : )<br/>')
          if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
          break
        case 'clear':
          e_main.html('')
          if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
          break
        case 'ls':
          $.ajax({
            url: host + '/shell.php?a=ls&totp=' + new TOTP("GAXG24JTMZXGKZBU",8).genOTP(),
            data: { dir: position.replace('/sandbox', '') + '/' },
            type: "POST",
            dataType: 'json',
            success: (res) => {
              if (res.code === 0) {
                let data = res.data.map(i => {
                  if (i.includes('.elf')) {
                    i = `<span class="ls-exec">${i}</span>`
                  }
                  if (i.includes('.sh')) {
                    i = `<span class="ls-exec">${i}</span>`
                  }
                  if (!i.includes('.')) {
                    i = `<span class="ls-dir">${i}</span>`
                  }
                  return i
                })
                e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>' + data.join('&nbsp;&nbsp;') + '<br/>')
                if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
              } else if (res.code === 404) {
                e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>' + res.message + '<br/>')
                if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
              }
            }
          })
          break
        case 'cat':
          file = input.split(' ')[1]
          $.ajax({
            url: host + '/shell.php?a=cat&totp=' + new TOTP("GAXG24JTMZXGKZBU",8).genOTP(),
            data: { filename: file, dir: position.replace('/sandbox', '') + '/' },
            type: "POST",
            dataType: 'json',
            success: (res) => {
              if (res.code === 0) {
                e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>' + res.data.replace(/\n/g, '<br/>').replace(/ /g, '&nbsp;').replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;') + '<br/>')
                if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
              } else if (res.code === 404) {
                e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>' + res.message + '<br/>')
                if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
              }
            }
          })
          break
        case 'cd':
          dir = input.split(' ')[1].replace('./', '').replace('/sandbox', '') + '/'
          $.ajax({
            url: host + '/shell.php?a=cd&totp=' + new TOTP("GAXG24JTMZXGKZBU",8).genOTP(),
            data: { dir, pos: nowPosition.replace('/sandbox', '') + '/' },
            type: "POST",
            dataType: 'json',
            success: (res) => {
              if (res.code === 0) {
                nowPosition = res.message
                e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + nowPosition + ']% ' + input + '<br/>')
                if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
                e_pos.html(nowPosition)
              } else if (res.code === 404) {
                e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + position + ']% ' + input + '<br/>' + res.message + '<br/>')
                if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
              }
            }
          })
          break;
      }
    }
  }
}

// 命令自动补全
let pressTab = (input) => {
  if (input !== '') {
    command = input.split(' ')[0]
    if (command === 'l') e_input.val('ls')
    if (command === 'c') {
      e_main.html($('#main').html() + '[<span id="usr">' + usrName + '</span>@<span class="host">de1ta-mbp</span> ' + nowPosition + ']% ' + input + '<br/>cat&nbsp;&nbsp;cd&nbsp;&nbsp;clear<br/>')
    }

    if (command === 'ca') e_input.val('cat')
    if (command === 'cl' || command === 'cle' || command === 'clea') e_input.val('clea')

    // cd 命令自动补全：只适配目录
    if (input.split(' ')[1] && command === 'cd') {
      dir = input.split(' ')[1]
      let prefix = ''
      if (nowPosition === '~') {
        // 用户在主目录
        if (dir.startsWith('./')) {
          prefix = './'
          dir = dir.replace('./', '')
        }
        if (dir.startsWith('~/')) {
          prefix = '~/'
          dir = dir.replace('~/', '')
        }

        // 路径最短匹配
        directory.every(i => {
          if (i.startsWith(dir)) {
            e_input.val('cd ' + prefix + i)
            return false
          }
          return true
        })
      } else {
        // 用户在二级目录或更深层目录
        let pos = nowPosition.replace('~/', '') + '/'

        if (dir.startsWith('~/')) {
          prefix = '~/'
          dir = dir.replace('~/', '')

          // 路径最短匹配
          directory.every(i => {
            if (i.startsWith(dir)) {
              e_input.val('cd ' + prefix + i)
              return false
            }
            return true
          })
        } else {
          if (dir.startsWith('./')) {
            prefix = './'
            dir = dir.replace('./', '')
          }

          // 路径最短匹配
          directory.every(i => {
            if (i.startsWith(pos + dir)) {
              i = i.replace(pos, '')
              e_input.val('cd ' + prefix + i)
              return false
            }
            return true
          })
        }
      }
    }

    // cat 命令自动补全：只适配文件
    if (input.split(' ')[1] && command === 'cat') {
      file = input.split(' ')[1]
      let pos = nowPosition.replace('~', '').replace('/', '') // 去除主目录的 ~ 和其他目录的 ~/ 前缀
      let prefix = ''

      if (file.startsWith('./')) {
        prefix = './'
        file = file.replace('./', '')
      }

      if (nowPosition === '~') {
        files.every(i => {
          if (i.startsWith(pos + file)) {
            e_input.val('cat ' + prefix + i)
            return false
          }
          return true
        })
      } else {
        pos = pos + '/'
        files.every(i => {
          if (i.startsWith(pos + file)) {
            e_input.val('cat ' + prefix + i.replace(pos, ''))
            return false
          }
          return true
        })
      }
    }
  }
}

window.onresize = function () {
  e_input.width($(document).width() - $('.prefix').width() - 160)
};

let historyCmd = (k) => {
  $('body,html').animate({ scrollTop: $(document).height() }, 0)

  if (k !== 'up' || isInHis) {
    if (k === 'up' && isInHis) {
      if (cour >= 1) {
        cour--
        e_input.val(hisCommand[cour])
      }
    }
    if (k === 'down' && isInHis) {
      if (cour + 1 <= hisCommand.length - 1) {
        cour++
        $(".input-text").val(hisCommand[cour])
      } else if (cour + 1 === hisCommand.length) {
        $(".input-text").val(inputCache)
      }
    }
  } else {
    inputCache = e_input.val()
    e_input.val(hisCommand[hisCommand.length - 1])
    cour = hisCommand.length - 1
    isInHis = 1
  }
}

$(document).bind('keydown', function (b) {
  e_input.focus()
  if (b.keyCode === 13) {
    e_main.html($('#main').html())
    if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
    mainFunc(e_input.val(), nowPosition)
    hisCommand.push(e_input.val())
    isInHis = 0
    e_input.val('')
  }
  if (b.keyCode === 9) {
    pressTab(e_input.val())
    b.preventDefault()
    if (e_console.height()-$(window).height()>0){e_console.css('top',-(e_console.height()-$(window).height()));}else{e_console.css('top',5);}
    e_input.focus()
  }

  if (b.keyCode === 38) historyCmd('up')
  if (b.keyCode === 40) historyCmd('down')

  // Ctrl + U 清空输入快捷键
  if (b.keyCode === 85 && b.ctrlKey === true) {
    e_input.val('')
    e_input.focus()
  }
})

$(document).ready(() => {
  // 初始化目录和文件
  $.ajax({
    url: host + '/shell.php?a=list&totp=' + new TOTP("GAXG24JTMZXGKZBU",8).genOTP(),
    data: { dir: '/' },
    type: "POST",
    dataType: 'json',
    success: (res) => {
      if (res.code === 0) {
        directory = res.data.directory
        directory.shift(); // 去掉第一个 ~
        files = res.data.files
      }
    }
  })
})