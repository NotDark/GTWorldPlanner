<?php $rev="rev=160917";?>
<!DOCTYPE html>
<html>
  <head>
    <title>Cernodile's Tools - World Planner</title>
    <script type="text/javascript" src="./js/planner/data.json?<?php echo $ev?>"></script>
    <style>
    * {
      font-family: "Century Gothic",Verdana,Arial;
      font-weight: bold;
    }
    body {
      background: black;
      overflow:hidden;
    }
      #slot {
        width: 32px;
        height: 32px;
        margin: 1px 3px;
        overflow:hidden;
        display: inline-block;
        border: 3px solid #77B4CC;
        position: relative;
        border-radius: 5px;
        background: #203940;
      }
      #slot img, span {
        user-drag: none;
        user-select: none;
        -moz-user-select: none;
        -webkit-user-drag: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        image-rendering: pixelated;
      }
      .background {
        border-color: #D3C87B !important;
      }
      .selected {
        border-color: #FDAA0C !important;
      }
      #inventory .btn {
        display: inline-block;
        background: #79b5c6;
        color: white;
        border-radius: 5px;
        margin: 3px;
        padding: 2px;
      }

#slot .tip {
  visibility: hidden;
      width: 170px;
      background-color: rgba(97, 141, 158, 0.8);
      color: #fff;
      text-align: center;
      border-radius: 6px;
      padding: 5px 0;
      position: fixed;
      z-index: 1;
      bottom: 20%;
      left: 50%;
      margin-left: -85px;
}

#slot:hover .tip {
    visibility: visible;
}
      #container canvas {
        position: absolute;
    top: 0;
    left: 0;
    image-rendering: pixelated;
zoom:1;
      }
      .load {
        width:0;
        height:0;
        position:absolute;
        z-index:-1;
      }
      #load {
        display:block;
        position:relative;
        top:0;
        color:#fff;
      }
      #bar,#alert {
        border: 2px solid white;
      }
      #alert span {
        display: block;
      }
      #alert #close {
        width: 50px;
        margin: 0.5em auto;
        border: 2px solid white;
      }
      a {
        text-decoration: none;
        color: #ccc;
      }
      #appDrawer {
        position: fixed;
        height: 100%;
        height: calc(100% - 32px);
        width: 40%;
        background-color: #3f3f3f;
        background: rgba(0, 0, 0, 0.68);
        z-index: 2;
        color: rgb(255, 255, 255);
        overflow: auto;
      }
      #appDrawer ul {
        padding: 0 0.5em;
      }
      #appDrawer ul * {
        float: left;
        clear: left;
      }
      #appDrawer ul li {
        cursor: pointer;
      }
      #menu h2 {
        display: inline-block;
        vertical-align: top;
        margin: 8px;
      }
      @media screen and (max-width: 425px) {
        #appDrawer {width:55%;}
        #appDrawer ul {margin-top:0;}
        #menu h2 {font-size: 1.25em;margin: 12px auto;}
      }
      @media screen and (min-width: 950px) {
        #appDrawer {width:30%;}
        #appDrawer ul {margin-top:0.5em;}
      }
    </style>
    <meta name="viewport" content="width=device-width, user-scalable=no, maximum-scale=1.0, target-densitydpi=device-dpi, initial-scale=1.0">
    <meta name="description" content="Web-based world planner for growtopiagame.com."/>
    <meta property="og:title" content="Cernodile's Tools - World Planner"/>
    <meta property="og:description" content="Web-based world planner for growtopiagame.com"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="https://tools.cernodile.com/planner.php"/>
    <meta property="og:image" content="https://tools.cernodile.com/assets/logo.png"/>
    <meta property="og:image:width" content="224"/>
    <meta property="og:image:height" content="224"/>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
  </head>
  <body style="margin:0;padding:0;">
    <div id="menu" style="color:white;">
      <img style="margin:8px;cursor:pointer;" onclick="openApp()" src="./assets/planner/menu.png">
      <h2>Cernodile's World Planner</h2>
    </div>
    <script>
    var appDrawer = false;
    var inventory = true;
    var cameraOnly = false;
    var loaded = false;
    var usingRect = false;
    var rectProgress = {};
var zoom = 1;
    function openApp () {
      if (!loaded) return;
      if (appDrawer) {
        appDrawer = false;
        document.getElementById("appDrawer").style.visibility="hidden";
        document.getElementById("cover").style.visibility="hidden";
      } else {
        appDrawer = true;
        document.getElementById("appDrawer").style.visibility="visible";
        document.getElementById("cover").style.visibility="visible";
      }
    }
    function hideInventory () {
      if (!inventory) {
        document.getElementById("container").style.height = "calc(75vh - 52px)";
        document.getElementById("inventory").style.height = "25vh";
        document.getElementById("version").style.bottom = "calc(22% + 52px)";
        inventory = true;
      } else {
        document.getElementById("container").style.height = "calc(100vh - 52px)";
        document.getElementById("inventory").style.height = "0";
        document.getElementById("version").style.bottom = "20px";
        inventory = false;
      }
    }
    function preset () {
      var select = document.getElementById("preset");
      var link = select.value;
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "./presets/" + link, true);
      xhr.onload = function (e) {
        if (xhr.status === 200) {
          loadSave(xhr.responseText);
        }
      }
      xhr.send();
    }
    function changeCam (el) {
      if (cameraOnly) {
        cameraOnly = false;
        el.innerText = "Camera Mode: Build";
      } else {
        cameraOnly = true;
        el.innerText = "Camera Mode: Move";
      }
    }
    var grid = false;
    function toggleGrid () {
      if (grid) {
        document.getElementById("grid").style.visibility = "hidden";
        grid = false;
      } else {
        document.getElementById("grid").style.visibility = "visible";
        grid = true;
      }
    }

    function rectangleToggle () {
      var el = document.getElementById("rect");
      if (usingRect) {
        var ddd = document.getElementById("planner").getContext("2d");
        ddd.clearRect(rectProgress["start"].x*boxS,rectProgress["start"].y*boxS,32,32);
        usingRect = false;
        el.style.color= "#ffffff";
        el.innerText = "Create Rectangle";
      } else {
        usingRect = true;
        rectProgress = {};
        el.style.color = "#929292";
        el.innerText = "Cancel Rectangle";
      }
    }
    </script>
    <div id="cover" onclick="openApp()" style="width: 100%;height:100%;background-color: #000;background: rgba(20, 20, 20, 0.54);position: absolute;z-index:2;visibility:hidden;"></div>
    <div id="appDrawer" style="visibility:hidden;">
      <ul style="list-style-type: none;overflow-y: visible;">
        <li onclick="countBlocks()">Count Blocks</li>
        <label>Change Weather <select id="weatherSelect" style="width: 70%;" onchange="changeWeather()">
          <option value="sunny">Sunny</option>
          <option value="night">Night</option>
          <option value="warp">Warp</option>
          <option value="arid">Arid</option>
          <option value="rainy">Rainy</option>
          <option value="spooky">Spooky</option>
          <option value="jungle">Jungle</option>
          <option value="snowy">Snowy</option>
          <option value="party">Party</option>
          <option value="snowy_night">Snowy Night</option>
          <option value="harvest">Harvest Blast</option>
          <option value="mars">Mars Blast</option>
          <option value="beach">Beach Blast</option>
          <option value="undersea">Undersea Blast</option>
          <option value="nothing">Nothingness</option>
          <option value="stuff_cobweb">Stuff - Cobweb</option>
          <option value="comet">Comet</option>
          <option value="howling_sky">Howling Sky</option>
          <option value="pagoda">Pagoda</option>
          <option value="spring">Spring</option>
          <option value="balloon">Balloon Warz</option>
        </select></label>
        <li onclick="hideInventory()">Toggle Inventory</li>
        <li onclick="toggleGrid()">Toggle Grid</li>
        <li onclick="changeCam(this)">Camera Mode: Build</li>
        <li onclick="render()">Render</li>
        <li id="zIn" onclick="zoomIn()">Zoom In (+50%)</li>
        <li id="zOut" onclick="zoomOut()">Zoom Out (-50%)</li>
        <li id="zoom" style="color: #929292;cursor:default;">Current zoom: 100%</li>
        <li onclick="rectangleToggle()" id="rect">Create Rectangle</li>
        <li onclick="save()">Save</li>
        <li onclick="clickFile()">Load <span style="color:red;float:none;">(Big worlds take long to load)</span></li>
        <label>Select preset: <select id="preset" style="width: 75%;float:none;" onchange="preset()">
          <optgroup label="Miscellanious">
            <option value="emptyworldnogrid.gtworld">Empty World [Default]</option>
            <option value="emptyworld.gtworld">Empty World with numbers</option>
            <option value="dirtworld.gtworld">Default dirt world</option>
            <option value="uglyComp.gtworld">topazi's ugly world competition winner - ChuckinDuckins</option>
          </optgroup>
          <optgroup label="World Replicas">
            <option value="parkour.gtworld">PARKOUR by Artemis</option>
            <option value="bretzraei.gtworld">BRETZRAEI by BretZraei</option>
            <option value="moneychanger.gtworld">MONEYCHANGER by MoneyChanger</option>
          </optgroup>
          <optgroup label="Event Worlds (TODO)">
            <option value="blarney1.gtworld">BLARNEY1 by @Hamumu</option>
            <option value="blarney2.gtworld">BLARNEY2 by @Hamumu</option>
          </optgroup>
        </select><br><span style="color:red;float:none;">(Slower devices may take a few minutes to process, don't freak out)</span></label>
        <input type="file" id="fileinput" accept=".gtworld" style="width:0;height:0;position:absolute;visibility:hidden;">
      </ul>
      <span id="version" style="bottom:2em;left:1%;display:block;color:#fff;position:absolute;pointer-events:none">Build ID <?php echo date("jmy-His", filemtime(__FILE__))."";?><br>Image material used belongs to Growtopia and Ubisoft.<br>Developed by Joann M&otilde;ndresku (@cernodile).</span>
    </div>
    <div id="container" style="position: relative;height: calc(75vh - 52px);width: 100%;overflow: scroll;margin:0 auto;background:#001414;">
      <canvas id="paintSheet" width="0" height="0" style="visibility:hidden;">.</canvas>
      <canvas id="background" width="3200" height="1920">Sorry, this world planner is not supported in your browser.</canvas>
      <canvas id="foreground" width="3200" height="1920">Sorry</canvas>
      <canvas id="grid" width="3200" height="1920" style="visibility:hidden;">Sorry</canvas>
      <canvas id="water" width="3200" height="1920">Sorry</canvas>
      <canvas id="planner" width="3200" height="1920">Sorry</canvas>
      <canvas id="render" width="1600" height="960" style="visibility:hidden;">Sorry</canvas>
    </div>
    <div id="loaded" style="position:absolute;background:#4cad6b;bottom:calc(60% + 5px);left:calc(30% + 5px);width:0%;height:1.25em;display:block;"></div>
    <div id="bar" style="bottom:60%;left:30%;display:block;color:white;position:absolute;pointer-events:none;padding:3px;width:37%;height:1.25em;text-align:center;"><span id="load">Loading Planner - Please wait!</span></div>
    <script>
      function loadedAsset (e) {
        setTimeout(() => {
          loadedAsset(e);
        }, 10);
      }
      function arrToLowerCase (a) {
        for (var itr = 0; itr < a.length; itr++) {
          a[itr] = a[itr].toLowerCase();
        }
        return a;
      }
      function query () {
        if (!loaded) return;
        var queryData = arrToLowerCase(Object.keys(data));
        var results = [];
        var priority = [];
        var value = document.getElementById("plannerSearch").value;
        for (var d in queryData) {
          if (queryData[d].includes(value.toLowerCase()) || data[Object.keys(data)[d]].priority) {
            if (data[Object.keys(data)[d]].priority) priority.push(Object.keys(data)[d]);
            results.push(Object.keys(data)[d]);
          }
        }
        if ((results.length - priority.length) > 0) {
          document.getElementById("inventory").innerHTML = `<form action="javascript:query()"><label>Search: <input id="plannerSearch" type="search" placeholder="Item"></label><input type="submit" value="Search"></form>`;
          document.getElementById("plannerSearch").value = value;
          for (var k in results) {
            var slot = document.createElement("div");
            slot.id = "slot";
            slot.dataset.name = results[k];
            slot.style["border-color"] = catColor(results[k]);
            var pos;
            switch(data[results[k]].spread) {
              case 3:
              case 7:
              case 9:
                pos = `top:-${(data[results[k]].y) * 32}px;left:-${(data[results[k]].x+3) * 32}px;`;
                break;
              case 6:
              case 8:
              case 1:
                pos = `top:-${(data[results[k]].y) * 32}px;left:-${(data[results[k]].x) * 32}px;`;
                break;
              case 4:
                pos = `top:-${(data[results[k]].y) * 32}px;left:-${(data[results[k]].x+4) * 32}px;`;
                break;
              default:
                pos = `top:-${(data[results[k]].y+1) * 32}px;left:-${(data[results[k]].x+4) * 32}px;`;
                break;
            }
            slot.innerHTML = `<span class="tip">${results[k]}</span><span onclick="javascript:switchBlock(\`${results[k]}\`);" style="${pos}position:relative;"><img preload id="${results[k]}" src="assets/${data[results[k]].file}"></span>`;
            document.getElementById("inventory").appendChild(slot);
          }
        }
      }
    </script>
    <div id="inventory" style="background:#3D6773;display: block;text-align:center;height:25vh;width:100%;overflow-y:scroll;margin:0 auto;">
      <form action="javascript:query()">
        <label>Search: <input id="plannerSearch" type="search" placeholder="Item"></label><input type="submit" value="Search">
      </form>
      <span id="invNotice" style="color: #fff;display: block;">Your inventory will appear once assets have been loaded...</span>
    </div>
    <img src="assets/amorkolg.png" id="amorkolg.png"class="load" onload="loadedAsset(this)">
    <img src="assets/bunting.png" id="bunting.png"class="load" onload="loadedAsset(this)">
    <img src="assets/vilpix.png" id="vilpix.png"class="load" onload="loadedAsset(this)">
    <img src="assets/water.png" id="water.png"class="load" onload="loadedAsset(this)">
    <img src="assets/fire.png" id="fire.png"class="load" onload="loadedAsset(this)">
    <img src="assets/sunny.png" id="sunny" class="load" onload="loadedAsset(this)">
    <img src="assets/night.png" id="night" class="load" onload="loadedAsset(this)">
    <img src="assets/arid.png" id="arid" class="load" onload="loadedAsset(this)">
    <img src="assets/rainy.png" id="rainy" class="load" onload="loadedAsset(this)">
    <img src="assets/spooky.png" id="spooky" class="load" onload="loadedAsset(this)">
    <img src="assets/snowy.png" id="snowy" class="load" onload="loadedAsset(this)">
    <img src="assets/snowy_night.png" id="snowy_night" class="load" onload="loadedAsset(this)">
    <img src="assets/warp.png" id="warp" class="load" onload="loadedAsset(this)">
    <img src="assets/pineapples.png" id="pineapples" class="load" onload="loadedAsset(this)">
    <img src="assets/spring.png" id="spring" class="load" onload="loadedAsset(this)">
    <img src="assets/undersea.png" id="undersea" class="load" onload="loadedAsset(this)">
    <img src="assets/mars.png" id="mars" class="load" onload="loadedAsset(this)">
    <img src="assets/beach.png" id="beach" class="load" onload="loadedAsset(this)">
    <img src="assets/harvest.png" id="harvest" class="load" onload="loadedAsset(this)">
    <img src="assets/nothing.png" id="nothing" class="load" onload="loadedAsset(this)">
    <img src="assets/comet.png" id="comet" class="load" onload="loadedAsset(this)">
    <img src="assets/howling_sky.png" id="howling_sky" class="load" onload="loadedAsset(this)">
    <img src="assets/pagoda.png" id="pagoda" class="load" onload="loadedAsset(this)">
    <img src="assets/stuff_cobweb.png" id="stuff_cobweb" class="load" onload="loadedAsset(this)">
    <img src="assets/jungle.png" id="jungle" class="load" onload="loadedAsset(this)">
    <img src="assets/party.png" id="party" class="load" onload="loadedAsset(this)">
    <img src="assets/balloon.png" id="balloon" class="load" onload="loadedAsset(this)">
    <img src="assets/paint.png" id="paint.png" class="load" onload="loadedAsset(this)">
    <img src="assets/pipes.png" id="pipes.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page1.png" id="tiles_page1.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page2.png" id="tiles_page2.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page3.png" id="tiles_page3.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page4.png" id="tiles_page4.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page5.png" id="tiles_page5.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page6.png" id="tiles_page6.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page7.png" id="tiles_page7.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page8.png" id="tiles_page8.png" class="load" onload="loadedAsset(this)">
    <img src="assets/tiles_page9.png" id="tiles_page9.png" class="load" onload="loadedAsset(this)">
    <script>
      function createNotif () {
        var cov = document.createElement("div");
        cov.id = "cov"
        cov.style["top"] = "0";
        cov.style["width"] = "100%";
        cov.style["height"] = "100%";
        cov.style["background-color"] = "#000";
        cov.style["background"] = "rgba(20,20,20,0.54)";
        cov.style["position"] = "absolute";
        cov.style["z-index"] = "2";
        document.body.appendChild(cov)
        var d = document.createElement("div");
        d.id = "notif";
        d.style.width = "50%";
        d.style.position = "absolute";
        d.style.top = "25%";
        d.style.left = "25%";
        d.style.color = "#ffffff";
        d.style.background = "#000000";
        d.style.padding = "10px";
        d.style["border-radius"] = "10px";
        d.style.width = "50%;"
        d.style["z-index"] = 5;
        d.innerHTML = "Thank you for choosing to use Cernodile's Growtopia World Planner! Countless hours have been spent to bring this to you free of charge, permanently. If you want to support my work, please consider <a href='https://www.growtopiagame.com/forums/showthread.php?441745'>leaving feedback and bumping my thread when neccesary</a>. Choosing later will re-notify you when you open this planner after 24 hours.<br><br><div style='border: 3px solid #fff;display:inline-block;padding:3px;cursor:pointer;border-radius: 10px;' onclick='neverAsk()'>Never ask me again</div><div style='border: 3px solid #fff;display:inline-block;padding: 3px;margin-left:10px;cursor: pointer;border-radius: 10px;' onclick='discardNotif()'>Later</div>";
        cov.appendChild(d);
      }
      function discardNotif () {
        document.getElementById("cov").remove();
      }
      function neverAsk () {
        localStorage.neverAsk = true;
        document.getElementById("cov").remove();
        var li = document.createElement("li");
        li.innerText = "Re-enable Daily Notification";
        li.addEventListener("click", function (e) {
          e.srcElement.remove();
          localStorage.neverAsk = false;
          delete localStorage.neverAsk;
        });
        document.getElementById("appDrawer").children[0].insertBefore(li, document.getElementById("appDrawer").children[0].children[0]);
      }
      function generateGrid () {
        if (!boxS) boxS = 32;
        var ddd = document.getElementById("grid").getContext("2d");
        ddd.clearRect(0,0,60*boxS,100*boxS);
        ddd.strokeStyle = "#FFFFFF";
        ddd.lineWidth = (boxS / 32) * 6 + "px";
        for (var i = 0; i < 60; i++) {
          for (var j = 0; j < 100; j++) {
            ddd.beginPath();
            ddd.rect((j*boxS),(i*boxS),((j+1)*boxS),((i+1)*boxS));
            ddd.stroke();
            ddd.closePath();
          }
        }
      }
      generateGrid();
      function drawRectangle (tY, tX, dX, dY, tiles, layer, ctx, type) {
        for (var tempY = tY;
          (tY <= dY ? tempY <= dY : tempY >= dY);
          (tY <= dY ? tempY++ : tempY--)) {
            for (var tempX = tX;
              (tX <= dX ? tempX <= dX : tempX >= dX);
              (tX <= dX ? tempX++ : tempX--)) {
                if (type === "Paint") {
                  buildBlock(tempX, tempY, "paint", false ,document.getElementById("planner").getContext("2d"), selected);
                } else if (type === "Flip") {
                  buildBlock(tempX, tempY, "flip", false, document.getElementById("planner").getContext("2d"));
                } else if (type === "Glue") {
                  buildBlock(tempX, tempY, "glue", false, document.getElementById("planner").getContext("2d"));
                } else {
                  tiles[tempY][tempX] = null;
                  buildBlock(tempX, tempY, layer, false, ctx);
                }
            }
        }
      }
      function catColor (name) {
        switch (data[name].cat) {
          case "Background Block":
          case "SFX Background":
          case "Toggleable Background":
          case "Sheet Music":
            return "#D3C87B";
          case "Lock":
            return "#FC6E0D";
          case "Flip":
          case "Glue":
          case "Water":
          case "Paint":
            return "#A95EC6";
          default:
            return "#77B4CC";
        }
      }
      function isBackground (name) {
        switch (data[name].cat) {
          case "Background Block":
          case "SFX Background":
          case "Toggleable Background":
          case "Sheet Music":
            return true;
          default:
            return false;
        }
      }
      function resizeCanvas () {
        document.getElementById("background").width = boxS * 100;
        document.getElementById("background").height = boxS * 60;
        document.getElementById("foreground").width = boxS * 100;
        document.getElementById("foreground").height = boxS * 60;
        document.getElementById("water").width = boxS * 100;
        document.getElementById("water").height = boxS * 60;
        document.getElementById("grid").width = boxS * 100;
        document.getElementById("grid").height = boxS * 60;
        document.getElementById("planner").width = boxS * 100;
        document.getElementById("planner").height = boxS * 60;
      }
      function zoomSet () {
         document.getElementById("background").style.zoom = zoom;
         document.getElementById("foreground").style.zoom = zoom;
         document.getElementById("water").style.zoom = zoom;
         document.getElementById("grid").style.zoom = zoom;
         document.getElementById("planner").style.zoom = zoom;
      }
      function zoomIn () {
        zoom += 0.5;
        if (zoom > 5) {
          zoom = 5;
        }
        document.getElementById("zoom").innerText = "Current zoom: " + zoom * 100 + "%";
        zoomSet();
      }
      function zoomOut () {
        zoom -= 0.5;
        if (zoom == 0) {
          zoom = 0.5;
        }
        if (zoom > 5) {
          zoom = 5;
        }
        document.getElementById("zoom").innerText = "Current zoom: " + zoom * 100 + "%";
        zoomSet();
      }
      function switchBlock (name) {
        selected = deconstructImage(name).clearName;
        var d = document.getElementsByClassName("selected");
        if (d.length > 0) {
          d[0].className = "";
          if (!document.getElementById(deconstructImage(selected).clearName)) return;
          document.getElementById(deconstructImage(selected).clearName).parentNode.parentNode.className = "selected";
        } else {
          if (!document.getElementById(deconstructImage(selected).clearName)) return;
          document.getElementById(deconstructImage(selected).clearName).parentNode.parentNode.className = "selected";
        }
      }
      var foreground = new Array();
      var background = new Array();
      var water = new Array();
      var glue = new Array();
      var flip = new Array();
      for (var i = 0; i < 60; i++) {
        var a = [];
        for (var d = 0; d < 100; d++) {
          a.push(null);
        }
        foreground.push(a.slice());
        background.push(a.slice());
        water.push(a.slice());
        glue.push(a.slice());
      }
      var canvas = document.getElementById("planner");
      canvas.oncontextmenu = function (e) {e.preventDefault();};
      var cont = document.getElementById("container");
      var weather = "sunny";
      var c = canvas.getContext("2d");
      var boxS = 32;
      var ld = 0;
      var maxLd = 35;
      var imgCache = {};
      var wState = -1;
      var last10th = 0;
      function changeWeather (w) {
        if (!w) {
          w = document.getElementById("weatherSelect").value;
        } else {
          document.getElementById("weatherSelect").value = w;
        }
        weather = w;
        cont.style.background = "url(assets/" + w + ".png)";
        cont.style["background-size"] = "cover";
      }
      function countBlocks () {
        var list = {};
        var paint = {"red":0,"yellow":0,"green":0,"aqua":0,"blue":0,"purple":0,"charcoal":0};
        var str = "Required items to build this world.\n\nFOREGROUND\n=====";
        for (var d in foreground) {
          for (var k in foreground[d]) {
            if (foreground[d][k] !== null) {
              if (deconstructImage(foreground[d][k]).paint) {
                paint[deconstructImage(foreground[d][k]).color]++;
              }
              if (list[deconstructImage(foreground[d][k]).clearName]) list[deconstructImage(foreground[d][k]).clearName]++;
              else list[deconstructImage(foreground[d][k]).clearName] = 1;
            }
          }
        }
        newL = {};
        Object.keys(list).sort().forEach(function(key) {
          newL[key] = list[key];
        });
        list = newL;
        delete newL;
        for (var d in list) {
          str += "\n" + d + ": " + list[d];
        }
        str += "\n\nBACKGROUND\n=====";
        list = {};
        for (var d in background) {
          for (var k in background[d]) {
            if (background[d][k] !== null) {
              if (deconstructImage(background[d][k]).paint) {
                if (foreground[d][k] === null) {
                  paint[deconstructImage(background[d][k]).color]++;
                }
              }
              if (list[deconstructImage(background[d][k]).clearName]) list[deconstructImage(background[d][k]).clearName]++;
              else list[deconstructImage(background[d][k]).clearName] = 1;
            }
          }
        }
        newL = {};
        Object.keys(list).sort().forEach(function(key) {
          newL[key] = list[key];
        });
        list = newL;
        delete newL;
        for (var d in list) {
          str += "\n" + d + ": " + list[d];
        }
        str += "\n\nMISC\n=====";
        list = {};
        for (var d in glue) {
          for (var k in glue[d]) {
            if (glue[d][k] !== null) {
              if (list[deconstructImage(glue[d][k]).clearName]) list[deconstructImage(glue[d][k]).clearName]++;
              else list[deconstructImage(glue[d][k]).clearName] = 1;
            } else if (water[d][k] !== null) {
              if (list[deconstructImage(water[d][k]).clearName]) list[deconstructImage(water[d][k]).clearName]++;
              else list[deconstructImage(water[d][k]).clearName] = 1;
            }
          }
        }
        for (var d in paint) {
          str += "\nPaint Bucket - " + d.charAt(0).toUpperCase() + d.substr(1) + ": " + paint[d];
        }
        newL = {};
        Object.keys(list).sort().forEach(function(key) {
          newL[key] = list[key];
        });
        list = newL;
        delete newL;
        for (var d in list) {
          str += "\n" + d + ": " + list[d];
        }
        var chunks = str.match(/(?:^.*$\n?){1,20}/mg);
        for (var d in chunks) {
          alert(chunks[d]);
        }
        return;
      }
      var done = 0;
      var paintCache = {};
      function createPaintCache (id, arr, max) {
        return new Promise((res, rej) => {
          if (!document.getElementById(id)) {
            console.log(id,arr,index,max)
          }
          arr[id] = {
            "purple": createColorSheet(document.getElementById(id), "purple", true),
            "red": createColorSheet(document.getElementById(id), "red", true),
            "yellow": createColorSheet(document.getElementById(id), "yellow", true),
            "green": createColorSheet(document.getElementById(id), "green", true),
            "aqua": createColorSheet(document.getElementById(id), "aqua", true),
            "blue": createColorSheet(document.getElementById(id), "blue", true),
            "charcoal": createColorSheet(document.getElementById(id), "charcoal", true),
            "varnish": document.getElementById(id)
          };
          done++;
          return res({finished: Math.floor(done / max * 100)});
        });
      }
      function loadedAsset(e) {
        ld++;
        imgCache[e.id] = e;
        if (last10th !== Math.floor(Math.floor(ld / maxLd * 100) / 10) / 10) {
          last10th = Math.floor(Math.floor(ld / maxLd * 100) / 10) / 10
          document.getElementById("loaded").style.width = Math.floor(ld / maxLd * 37) + "%";
          document.getElementById("load").innerHTML = "Loading assets into memory... (" + (last10th*100) + "%)";
        }
        if (ld === maxLd) {
          document.getElementById("loaded").style.width = "37%";
          document.getElementById("load").innerHTML = "Ensuring files are loaded (1.5 seconds to process)";
          setTimeout(() => {
            document.getElementById("load").innerHTML = "Creating paint cache - Might take a bit.<br><span style='font-size:0.75em;'>(Load time depends on your processor power)</span>";
          }, 1400);
          setTimeout(() => {
            loadPlanner();
          }, 1500);
        }
      }
      function loadPlanner () {
        var amt = [];
        Object.values(data).filter(d => (amt.indexOf(d.file) > -1 ? true : amt.push(d.file)));

        for (var d in amt) {
              createPaintCache(amt[d], paintCache, amt.length).then(r => {
                if (r.finished == 100) {
                  document.getElementById("bar").remove();
                  document.getElementById("loaded").remove();
                  document.getElementById("invNotice").remove();
                  changeWeather("sunny");
                  for (var k in data) {
                    var slot = document.createElement("div");
                    slot.id = "slot";
                    slot.dataset.name = k;
                    slot.style["border-color"] = catColor(k);
                    var pos;
                    switch(data[k].spread) {
                      case 3:
                      case 7:
                      case 9:
                        pos = `top:-${(data[k].y) * 32}px;left:-${(data[k].x+3) * 32}px;`;
                        break;
                      case 6:
                      case 8:
                      case 1:
                        pos = `top:-${(data[k].y) * 32}px;left:-${(data[k].x) * 32}px;`;
                        break;
                      case 4:
                        pos = `top:-${(data[k].y) * 32}px;left:-${(data[k].x+4) * 32}px;`;
                        break;
                      default:
                        pos = `top:-${(data[k].y+1) * 32}px;left:-${(data[k].x+4) * 32}px;`;
                        break;
                    }
                    slot.innerHTML = `<span class="tip">${k}</span><span onclick="javascript:switchBlock(\`${k}\`);" style="${pos}position:relative;"><img preload id="${k}" src="assets/${data[k].file}"></span>`;
                    document.getElementById("inventory").appendChild(slot);
                  }
                  for (var row = 0; row < 60; row++) {
                      for (var col = 0; col < 100; col++) {
                          var x = col * boxS;
                          var y = (row) * boxS;
                          if (row === 53 && col === 49) foreground[row][col] = "Main Door";
                          if (row === 52 && col === 49) foreground[row][col] = "Diamond Lock";
                          if (row > 53) {
                            foreground[row][col] = "Bedrock";
                            background[row][col] = "Cave Background";
                            if (row === 59 && col === 99) reDraw();
                          }
                      }
                  }
                  //canvas.addEventListener("touchmove", handleMove); // Maybe later...
                  if (localStorage.nextNotif && !localStorage.neverAsk) {
                    if (localStorage.nextNotif <= Date.now()) {
                      createNotif();
                      localStorage.nextNotif = Date.now() + (1000 * 60 * 60 * 24);
                    }
                  } else {
                    if (!localStorage.neverAsk) {
                      localStorage.nextNotif = Date.now() + (1000 * 60 * 60 * 24);
                      createNotif();
                    } else {
                      var li = document.createElement("li");
                      li.innerText = "Re-enable Daily Notification";
                      li.addEventListener("click", function (e) {
                        e.srcElement.remove();
                        localStorage.neverAsk = false;
                        delete localStorage.neverAsk;
                      });
                      document.getElementById("appDrawer").children[0].insertBefore(li, document.getElementById("appDrawer").children[0].children[0]);
                    }
                  }
                  canvas.addEventListener("touchstart", function (e) {(cameraOnly ? false : handleClick(e, false, true))});
                  canvas.addEventListener("mousemove", handleMove);
                  canvas.addEventListener("mouseup", function (e) {
                    if (usingRect) {
                      var x = Math.floor(e.offsetX / (boxS * (zoom)));
                      var y = Math.floor(e.offsetY / (boxS * (zoom)));
                      if (x !== rectProgress["start"].x || y !== rectProgress["start"].y) {
                        handleClick(e);
                      }
                    }
                  });
                  canvas.addEventListener("mousedown", handleClick);
                  loaded = true;
                  return;
                }
              });
        }
      }
      function save () {
	      var a = document.createElement('a');
        a.style.visibility = "hidden";
        document.body.appendChild(a);
        var blob = new Blob(["%cernworldplanner;\nweather="+weather+"\nfg="+foreground.join("\n") + "\nbg="+background.join("\n")+"\nwater="+water.join("\n")+"\nglue="+glue.join("\n")], {'type':'application/octet-stream'});
        var ur = (window.webkitURL ? window.webkitURL : window.URL);
        a.href = ur.createObjectURL(blob);
        a.download = "world.gtworld";
        a.click();
        a.remove();
      }
      function loadSave (c) {
        var content = c.split("\n");
        weather = c.split("weather=")[1].split("\nfg=")[0];
        if (weather === "nothingness") weather = "nothing"; // Backwards compatibility.
        changeWeather(weather);
        var bg = c.split("bg=")[1].split("\nwater=")[0].split("\n");
        var fg = c.split("fg=")[1].split("\nbg=")[0].split("\n");
        var wat = c.split("water=")[1].split("\nglue=")[0].split("\n");
        var gl = c.split("glue=")[1].split("\n");
        for (var d in bg) {
          bg[d] = bg[d].split("\n");
          fg[d] = fg[d].split("\n");
          wat[d] = wat[d].split("\n");
          gl[d] = gl[d].split("\n");
          for (var k in bg[d]) {
            bg[d] = bg[d][k].split(",");
            fg[d] = fg[d][k].split(",");
            wat[d] = wat[d][k].split(",");
            gl[d] = gl[d][k].split(",");
            for (var g in bg[d]) {
              if (bg[d][g] === "") bg[d][g] = null;
              if (fg[d][g] === "") fg[d][g] = null;
              if (gl[d][g] === "") gl[d][g] = null;
              if (wat[d][g] === "") wat[d][g] = null;
            }
          }
        }
        foreground = fg;
        background = bg;
        water = wat;
        glue = gl;
        reDraw(true);
      }
      function readSingleFile(evt) {
      	var f = evt.target.files[0];
      	if (f) {
        	var r = new FileReader();
        	r.onload = function(e) {
  	      	var contents = e.target.result;
  					if (f.name.endsWith(".gtworld")) {
  						if (contents.startsWith("%cernworldplanner;")) {
  							loadSave(contents);
  						} else alert("Corrupt .gtworld file");
  					} else alert("Please load a .gtworld file!");
        	}
        	r.readAsText(f);
      	} else {
        	alert("No file selected.");
      	}
    	}
  		function clickFile () {
  			document.getElementById('fileinput').click();
  		}
    	document.getElementById('fileinput').addEventListener('change', readSingleFile, false);
      cont.scrollTop = cont.scrollHeight - cont.clientHeight;
      var selected = "Dirt";
      var curX = 0;
      var curY = 0;
      var oldX = 0;
      var oldY = 0;
      var prevAction = "place";
      function handleMove(e) {
        if (cameraOnly && e.buttons == 1) {
          document.getElementById("container").scrollTop -= e.movementY;
          document.getElementById("container").scrollLeft -= e.movementX;
        } else {
          if (usingRect) {
            e.preventDefault();
            return;
          }
          if (!cameraOnly) e.preventDefault();
          oldX = curX;
          oldY = curY;
          curX = Math.floor(e.offsetX / (boxS * (zoom)));
          curY = Math.floor(e.offsetY / (boxS * (zoom)));
          if (curX != oldX || curY != oldY) {
            handleClick(e, true);
          }
        }
      }
      function handleClick(e, checkFirst,touch) {
        e.preventDefault();
        if (cameraOnly) return;
        if (e.changedTouches) {
          var rect = e.target.getBoundingClientRect();
          e.offsetX = Math.floor(e.changedTouches[0].pageX - (rect.left*zoom));
          e.offsetY = Math.floor(e.changedTouches[0].pageY - (rect.top*zoom));
        }
        var x = Math.floor(e.offsetX / (boxS * (zoom)));
        var y = Math.floor(e.offsetY / (boxS * (zoom)));
        if (!touch) {
          if (e.buttons !== undefined) {
            if (e.buttons !== 1) {
              if (e.buttons === 2) {
                if (foreground[y][x]) {
                  return switchBlock(foreground[y][x]);
                } else if (background[y][x]) {
                  return switchBlock(background[y][x]);
                } else return;
              } else if (e.which === 2) {
                if (foreground[y][x]) {
                  return switchBlock(foreground[y][x]);
                } else if (background[y][x]) {
                  return switchBlock(background[y][x]);
                } else return;
              }
              if (navigator.appCodeName === "Mozilla" && !!navigator.platform.match(/win|mac/ig) === true && e.type !== "mouseup") return;
            }
          } else if (e.which !== 1) return;
        }
        var type = data[selected].cat;
        if (!isBackground(selected) && type !== "Water") {
          tmpL = foreground;
          tmpLs = "fg";
          tmpLL = "foreground";
        } else if (type !== "Water") {
          tmpL = background;
          tmpLs = "bg";
          tmpLL = "background";
        } else {
          tmpL = water;
          tmpLs = "water";
          tmpLL = "water";
        }
        if (usingRect) {
          if (!rectProgress["start"]) {
            rectProgress["start"] = {x, y};
            var ddd = document.getElementById("planner").getContext("2d");
            ddd.fillStyle = "rgba(255,255,255,0.6)";
            ddd.beginPath();
            ddd.fillRect((x*boxS),(y*boxS),32,32);
            ddd.closePath();
          } else {
            var ddd = document.getElementById("planner").getContext("2d");
            ddd.clearRect(rectProgress["start"].x*boxS,rectProgress["start"].y*boxS,32,32);
            rectProgress["end"] = {x, y};
            drawRectangle(rectProgress["start"].y, rectProgress["start"].x, x, y, tmpL, tmpLs, document.getElementById(tmpLL).getContext("2d"), type);
            rectangleToggle();
          }
          return;
        }
          c.fillStyle = "black";
            if (type === "Glue") {
              return buildBlock(x, y, "glue", false, document.getElementById("planner").getContext("2d"));
            }
            if (type === "Flip") {
              return buildBlock(x, y, "flip", false, document.getElementById("planner").getContext("2d"));
            }
            if (type === "Paint") {
              return paintBlock(x,y,selected);
            }
            if (checkFirst) {
              if (tmpL[y][x] !== null) {
                if (deconstructImage(tmpL[y][x]).clearName === selected) {
                  if (prevAction === "place") {
                    return;
                  }
                  buildBlock(x, y, tmpLs, false, document.getElementById(tmpLL).getContext("2d"));
                } else {
                  if (prevAction === "delete") {
                    return;
                  }
                  buildBlock(x, y, tmpLs, false, document.getElementById(tmpLL).getContext("2d"));
                }
              } else {
                if (prevAction === "delete") {
                  return;
                }
                buildBlock(x, y, tmpLs, false, document.getElementById(tmpLL).getContext("2d"));
              }
            } else {
              if (tmpL[y][x] === null) {
                prevAction = "place";
                buildBlock(x, y, tmpLs, false, document.getElementById(tmpLL).getContext("2d"));
              } else {
                if (deconstructImage(tmpL[y][x]).clearName === selected) {
                  prevAction = "delete";
                } else {
                  prevAction = "place";
                }
                buildBlock(x, y, tmpLs, false, document.getElementById(tmpLL).getContext("2d"));
              }
            }
            var revL = (tmpLs === "bg" ? foreground : background);
            if (revL[y][x] !== null && tmpLs !== "water") {
              revLs = (tmpLs === "bg" ? "fg" : "bg");
              revLL = (tmpLs === "bg" ? "foreground" : "background");
              oldSel = selected;
              selected = "Paint Bucket - Varnish";
              buildBlock(x, y, "paint", false, document.getElementById("planner").getContext("2d"),selected);
              selected = oldSel;
            }
      }
      function paintBlock (x,y,paint) {
        buildBlock(x,y,"paint",false,document.getElementById("planner").getContext("2d"),paint);
      }
      function glue (x,y,layer,rb,c) {
        buildBlock(x,y,layer,rb,c);
        selected = "Block Glue";
      }
      function deconstructImage (name) {
        // Deconstruct image to object with properties and target.
        var cList = {
          "P": "purple",
          "R": "red",
          "Y": "yellow",
          "G": "green",
          "A": "aqua",
          "B": "blue",
          "C": "charcoal",
          "V": "varnish"
        };
        var obj = {};
        obj.unparsed = name;
        obj.flip = (name.endsWith("_FL") ? true : false);
        obj.paint = (name.match(/P[PRYGABCV]_/g) ? true : false);
        obj.clearName = (obj.flip === true ? name.substr((obj.paint === true ? 3 : 0), name.length - (obj.paint === true ? 6 : 3)) : name.substr((obj.paint === true ? 3 : 0)));
        obj.color = cList[name.charAt(1)];
        if (data[obj.clearName])
          obj.image = imgCache[location.origin + "/assets/" + data[obj.clearName].file];
        else obj.image = null;
        obj.flipName = obj.clearName;
        if (obj.flip) {
          obj.flipName = obj.clearName + "_FL";
        }
        obj.isBackground = isBackground(obj.clearName);
        return obj;
      }
      function buildBlock (x, y, layer, rb, c, p) {
        c.globalCompositeOperation = "source-over";
        c.imageSmoothingEnabled = false;
        type = layer;
        layer = (layer === "bg" ? background : foreground);
        if (type === "water") {
          layer = water;
          c.globalAlpha = 0.7;
        }
        glu = selected;
        lock = false;
        if (type === "flip") {
          rb = true;
          if (foreground[y][x] !== null) {
            if (data[deconstructImage(foreground[y][x]).clearName].flip === 1 && !foreground[y][x].endsWith("_FL")) {
              foreground[y][x] = foreground[y][x] + "_FL"
              layer = foreground;
              type = "fg";
              c = document.getElementById("foreground").getContext("2d");
              selected = foreground[y][x];
            } else {
              if (foreground[y][x].endsWith("_FL")) {
                foreground[y][x] = foreground[y][x].substr(0, foreground[y][x].indexOf("_FL"));
                layer = foreground;
                type = "fg";
                c = document.getElementById("foreground").getContext("2d");
                selected = foreground[y][x];
              } else return;
            }
          } else if (background[y][x] !== null) {
            if (data[deconstructImage(background[y][x]).clearName].flip === 1 && !background[y][x].endsWith("_FL")) {
              background[y][x] = background[y][x] + "_FL"
              layer = background;
              type = "bg";
              c = document.getElementById("background").getContext("2d");
              selected = background[y][x];
            } else {
              if (background[y][x].endsWith("_FL")) {
                background[y][x] = background[y][x].substr(0, background[y][x].indexOf("_FL"));
                layer = background;
                type = "bg";
                c = document.getElementById("background").getContext("2d");
                selected = background[y][x];
              } else return;
            }
          } else return;
        }
        if (type === "paint") {
          rb =true;
        //  lock = true;
          if (foreground[y][x] !== null) {
            var prop = deconstructImage(foreground[y][x]);
            if (p.split("- ")[1].charAt(0) !== "V") {
              if (!prop.paint) foreground[y][x] = "P" + p.split("- ")[1].charAt(0) + "_" + foreground[y][x];
              else {
                foreground[y][x] = foreground[y][x].split("");
                foreground[y][x][1] = p.split("- ")[1].charAt(0);
                foreground[y][x] = foreground[y][x].join("");
              }
            } else foreground[y][x] = prop.flipName;
            layer = foreground;
            type = "fg";
            c = document.getElementById("foreground").getContext("2d");
            if (prop.clearName === "Display Block") {
              foreground[y][x] = prop.flipName;
              return;
            }
            selected = foreground[y][x];
          }
          if (background[y][x] !== null) {
            var prop = deconstructImage(background[y][x]);
            if (p.split("- ")[1].charAt(0) !== "V") {
              if (!prop.paint) background[y][x] = "P" + p.split("- ")[1].charAt(0) + "_" + background[y][x];
              else {
                background[y][x] = background[y][x].split("");
                background[y][x][1] = p.split("- ")[1].charAt(0);
                background[y][x] = background[y][x].join("");
              }
            } else background[y][x] = prop.flipName;
            if (type === "paint") {
              layer = background;
              type = "bg";
              c = document.getElementById("background").getContext("2d");
              selected = background[y][x];
            }
          }
          if (foreground[y][x] === null && background[y][x] === null) {
            selected = glu;return;
          }
        }
        c.globalCompositeOperation = "source-over";
        deletedGlue = false;
        if (type === "glue") {
          rb = true;
          if (glue[y][x] !== null) {
            glue[y][x] = null;
            deletedGlue = true;
          } else glue[y][x] = "Block Glue";
          if (foreground[y][x] !== null) {
            layer = foreground;
            type = "fg";
            c = document.getElementById("foreground").getContext("2d");
            selected = foreground[y][x];
          } else if (background[y][x] !== null) {
            layer = background;
            type = "bg";
            c = document.getElementById("background").getContext("2d");
            selected = background[y][x];
          } else {
            selected = glu;return;
          }
        }
        var grid = [
          [0, 0, 0],
          [0, 1, 0],
          [0, 0, 0]
        ];
        for (var k in grid) {
          var tempY = y-1 + parseInt(k);
          for (var d in grid[k]) {
            var tempX = x-1 + parseInt(d);
            if (tempX === -1 || tempX === 100) {
              grid[k][d] = 2;
            } else if (tempY === 60 || tempY === -1) {
              grid[k][d] = 2;
            } else if (layer[tempY][tempX] !== null)
                if (deconstructImage(layer[tempY][tempX]).flipName === deconstructImage(selected).flipName)
                  grid[k][d] = 1;
                else if (glue[tempY][tempX] !== null && type !== "water")
                  grid[k][d] = 2;
          }
        }
        if (!rb) {
          if (layer[y][x] !== null) {
            if (layer[y][x] === selected) {
              layer[y][x] = null;
              c.beginPath();
              c.clearRect(x*boxS, y*boxS, boxS, boxS);
              c.closePath();
              lock = true;
            } else {
              layer[y][x] = selected;
            }
          } else layer[y][x] = selected;
        }
        var t = deconstructImage(selected).clearName;
        var prop = deconstructImage(selected);
        var spread = data[t].spread;
        c.beginPath();
        c.clearRect(x*boxS, y*boxS, boxS, boxS);
        c.closePath();
        imgLoc = getSpread(spread, grid, selected,x,y);
        if (selected.endsWith("_FL")) {
          c.scale(-1, 1);
          if (spread === 3) {
            if (imgLoc.x === 0) imgLoc.x = 2;
            else if (imgLoc.x === 2) imgLoc.x = 0;
          }
        }
        var act = imgCache[data[t].file];
        if (!lock) {
          if (p) {
            c.drawImage(paintCache[data[t].file][p.split("- ")[1].toLowerCase()], (data[t].x+imgLoc.x) * 32, (data[t].y+imgLoc.y) * 32, 32, 32, (prop.flip ? -(x+1) :  x) * boxS, y * boxS, boxS, boxS);
            var tLayer = (type === "bg" ? foreground : background);
            if (tLayer[y][x] !== null) {
              tC = document.getElementById((type === "bg" ? "foreground" : "background")).getContext("2d");
              tSel = tLayer[y][x];
              tT = deconstructImage(tSel);
              tSpread = data[tT.clearName].spread;
              tImgLoc = getSpread(tSpread,makeGrid(x,y,tLayer,tT.clearName),tSel,x,y);
              if (tT.clearName !== "Display Block") {
                tC.beginPath();
                tC.clearRect(x*boxS, y*boxS, boxS,boxS);
                tC.closePath();
                tC.drawImage(paintCache[data[tT.clearName].file][p.split("- ")[1].toLowerCase()], (data[tT.clearName].x+tImgLoc.x) * 32, (data[tT.clearName].y+tImgLoc.y) * 32, 32, 32, (tT.flip ? -(x+1) :  x) * boxS, y * boxS, boxS, boxS);
              }
            }
          } else {
            if (deconstructImage(selected).paint)
              c.drawImage(paintCache[data[t].file][deconstructImage(selected).color], (data[t].x+imgLoc.x) * 32, (data[t].y+imgLoc.y) * 32, 32, 32, (prop.flip ? -(x+1) :  x) * boxS, y * boxS, boxS, boxS);
            else c.drawImage(imgCache[data[t].file], (data[t].x+imgLoc.x) * 32, (data[t].y+imgLoc.y) * 32, 32, 32, (prop.flip ? -(x+1) :  x) * boxS, y * boxS, boxS, boxS);
          }
        }
        if (selected.endsWith("_FL")) c.scale(-1, 1);
        if (data[t].cat === "Steam Block" && !lock || data[t].cat === "Steam Music Block" && !lock) {
          spread = 2;
          var imgLoc = getSpread(spread, grid,t,x,y);
          if (selected.endsWith("_FL")) {
            imgLoc.y++;
          }
          c.drawImage(document.getElementById(data["Steam Tubes"].file), (8+imgLoc.x) * 32, (0+imgLoc.y) * 32, 32, 32, (x) * boxS, (y) * boxS, boxS, boxS);
        }

          c.globalCompositeOperation = "source-over";
          for (var k in grid) {
            var tempY = y-1 + parseInt(k);
            for (var d in grid[k]) {
              var tempX = x-1 + parseInt(d);
              if (grid[k][d] === 1 && k+","+d !== "1,1") {
                c.beginPath();
                c.clearRect(tempX*boxS, tempY*boxS, boxS, boxS);
                c.closePath();
                var prop = deconstructImage(layer[tempY][tempX]);
                var spread = data[prop.clearName].spread;
                if (prop.paint) act = paintCache[data[prop.clearName].file][prop.color];
                else act = imgCache[data[prop.clearName].file]
                var imgLoc = getSpread(spread, makeGrid(tempX, tempY, layer, prop.flipName, grid[k][d]),prop.flipName,tempX,tempY);
                if (prop.flip) {
                  c.scale(-1, 1);
                  if (spread === 3) {
                    if (imgLoc.x === 0) imgLoc.x = 2;
                    else if (imgLoc.x === 2) imgLoc.x = 0;
                  }
                }
                c.drawImage(act, (data[prop.clearName].x+imgLoc.x) * 32, (data[prop.clearName].y+imgLoc.y) * 32, 32, 32, (prop.flip ? -(tempX+1) :  tempX) * boxS, (tempY) * boxS, boxS, boxS);
                if (prop.flip) c.scale(-1, 1);
                if (data[prop.clearName].cat === "Steam Block" || data[prop.clearName].cat === "Steam Music Block") {
                  spread = 2;
                  var imgLoc = getSpread(spread, makeGrid(tempX, tempY, layer, selected, grid[k][d]),prop.clearName,tempX,tempY);
                  c.drawImage(document.getElementById(data["Steam Tubes"].file), (8+imgLoc.x) * 32, (0+imgLoc.y) * 32, 32, 32, (tempX) * boxS, (tempY) * boxS, boxS, boxS);
                }
              } else if (k+","+d !== "1,1") {
                if (layer[tempY]) {
                  if (layer[tempY][tempX] !== null && layer[tempY][tempX]) {
                    var prop = deconstructImage(layer[tempY][tempX]);
                    var spread = data[prop.clearName].spread;
                    if (prop.paint) act = paintCache[data[prop.clearName].file][prop.color];
                    else act = imgCache[data[prop.clearName].file];
                    c.beginPath();
                    c.clearRect(tempX*boxS, tempY*boxS, boxS, boxS);
                    c.closePath();
                    var imgLoc = getSpread(spread, makeGrid(tempX, tempY, layer, prop.flipName, (deletedGlue ? 2 : 0)), prop.flipName,tempX,tempY);
                    if (prop.flip) {
                      c.scale(-1, 1);
                      if (spread === 3) {
                        if (imgLoc.x === 0) imgLoc.x = 2;
                        else if (imgLoc.x === 2) imgLoc.x = 0;
                      }
                    }
                    c.drawImage(act, (data[prop.clearName].x+imgLoc.x) * 32, (data[prop.clearName].y+imgLoc.y) * 32, 32, 32, (prop.flip ? -(tempX+1) :  tempX) * boxS, (tempY) * boxS, boxS, boxS);
                    if (prop.flip) c.scale(-1, 1);
                    if (data[prop.clearName].cat === "Steam Block" || data[prop.clearName].cat === "Steam Music Block") {
                      spread = 2;
                      var imgLoc = getSpread(spread, makeGrid(tempX, tempY, layer, prop.clearName, (deletedGlue ? 2 : 0)),prop.clearName,tempX,tempY);
                      c.drawImage(document.getElementById(data["Steam Tubes"].file), (8+imgLoc.x) * 32, (0+imgLoc.y) * 32, 32, 32, (tempX) * boxS, (tempY) * boxS, boxS, boxS);
                    }
                  }
                }
              } else {
                if (glue[y]) {
                  if (glue[y][x] !== null && layer[tempY][tempX]) {
                    var prop = deconstructImage(layer[tempY][tempX]);
                    var spread = data[prop.clearName].spread;
                    if (prop.paint) act = paintCache[data[prop.clearName].file][prop.color];
                    else act = imgCache[data[prop.clearName].file];

                    c.beginPath();
                    c.clearRect(tempX*boxS, tempY*boxS, boxS, boxS);
                    c.closePath();
                    var imgLoc = getSpread(spread, makeGrid(tempX, tempY, layer, prop.flipName, (deletedGlue ? 2 : 0)), prop.flipName,tempX,tempY);
                    if (prop.flip) {
                      c.scale(-1, 1);
                      if (spread === 3) {
                        if (imgLoc.x === 0) imgLoc.x = 2;
                        else if (imgLoc.x === 2) imgLoc.x = 0;
                      }
                    }
                    c.drawImage(act, (data[prop.clearName].x+imgLoc.x) * 32, (data[prop.clearName].y+imgLoc.y) * 32, 32, 32, (prop.flip ? -(tempX+1) :  tempX) * boxS, (tempY) * boxS, boxS, boxS);
                    if (prop.flip) c.scale(-1, 1);
                    if (data[prop.clearName].cat === "Steam Block" || data[prop.clearName].cat === "Steam Music Block") {
                      spread = 2;
                      var imgLoc = getSpread(spread, makeGrid(tempX, tempY, layer, prop.clearName, (deletedGlue ? 2 : 0)),prop.clearName,tempX,tempY);
                      c.drawImage(document.getElementById(data["Steam Tubes"].file), (8+imgLoc.x) * 32, (0+imgLoc.y) * 32, 32, 32, (tempX) * boxS, (tempY) * boxS, boxS, boxS);
                    }
                  }
                }
              }
              if (parseInt(k) === 2 && parseInt(d) === 2) selected = deconstructImage(glu).clearName;
            }
          }
      }
      function createColorSheet (file, color,saveImg) {
        if (!imgCache[file.src]) {
          imgCache[file.src] = document.getElementById(file.id);
        }
        if (color === "varnish") return imgCache[file.src];
        var tmC = document.getElementById("paintSheet");
        tmC.width = file.naturalWidth;
        tmC.height = file.naturalHeight;
        var tCtx = tmC.getContext("2d");
        tCtx.globalCompositeOperation = "multiply";
        var matchColor = {
          "purple": "FF3CFF",
          "red": "FF3C3C",
          "yellow": "FFFF3C",
          "green": "3CFF3C",
          "aqua": "3CFFFF",
          "blue": "3C3CFF",
          "charcoal": "3C3C3C",
        };
        tCtx.fillStyle = "#" + matchColor[color];
        tCtx.drawImage(imgCache[file.src], 0, 0);
        tCtx.fillRect(0,0, tmC.width, tmC.height);
        tCtx.globalCompositeOperation = "destination-atop";
        tCtx.drawImage(imgCache[file.src], 0, 0);

        if (saveImg) {
          if (saveImg) {
            var cache = document.createElement('canvas');
            cache.width = tmC.width;
            cache.height = tmC.height;
            cache.getContext("2d").drawImage(tmC,0,0);
            return cache;
          }
        }
        return document.getElementById("paintSheet");
      }
      function makeGrid (x, y, layer, sel, gr) {
        var grid = [
          [0, 0, 0],
          [0, 1, 0],
          [0, 0, 0]
        ];
        if (!sel) sel = selected;
        for (var k in grid) {
          var tempY = y-1 + parseInt(k);
          for (var d in grid[k]) {
            var tempX = x-1 + parseInt(d);
            if (tempX === -1 || tempX === 100) {
              grid[k][d] = 2;
            } else if (tempY === 60 || tempY === -1) {
              grid[k][d] = 2;
            } else if (layer[tempY])
              if (layer[tempY][tempX]) {
                if (deconstructImage(layer[tempY][tempX]).flipName === deconstructImage(sel).flipName)
                  grid[k][d] = 1;
                else if (glue[tempY][tempX] !== null && layer[tempY][tempX] !== null && type !== "water")
                  grid[k][d] = 2;
                else if (gr === 2 && tempX === x && tempY === y && layer[tempY][tempX] !== null && type !== "water")
                  grid[k][d] = 2;
                else {
                  if (glue[y][x] !== null && type !== "water") {
                    if (k === 0 && d === 1 || k === 1 || k === 2 && d === 1) grid[k][d] = 2;
                  }
                }
              }
          }
        }
        return grid;
      }
      function b64toBlob(b64Data, contentType, sliceSize) {
  contentType = contentType || '';
  sliceSize = sliceSize || 512;

  var byteCharacters = atob(b64Data);
  var byteArrays = [];

  for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
    var slice = byteCharacters.slice(offset, offset + sliceSize);

    var byteNumbers = new Array(slice.length);
    for (var i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }

    var byteArray = new Uint8Array(byteNumbers);

    byteArrays.push(byteArray);
  }

  var blob = new Blob(byteArrays, {type: contentType});
  return blob;
}
      function render () {
        var ctx = document.getElementById("render").getContext("2d");
        ctx.imageSmoothingEnabled = false;
        ctx.drawImage(imgCache[weather], 0, 0, 1600, 960);
        ctx.drawImage(document.getElementById("background"), 0, 0, 1600, 960);
        ctx.drawImage(document.getElementById("foreground"), 0, 0, 1600, 960);
        ctx.drawImage(document.getElementById("water"), 0, 0, 1600, 960);
        var a = document.createElement('a');
        a.style.visibility = "hidden";
        document.body.appendChild(a);
        a.href = document.getElementById("render").toDataURL();
        a.download = "renderworld.png";
        if (window.navigator.userAgent.indexOf("Edge") === -1) {
          a.click();
          a.remove();
        } else {
          var blob = new Blob([b64toBlob(document.getElementById("render").toDataURL().replace(/^data:image\/(png|jpg);base64,/, ""),"image/png")], {type: "image/png"});
          navigator.msSaveBlob(blob, "renderworld.png");
        }
      }
      function reDraw (save) {
        startSel = (selected ? selected : "Dirt");
        if (save) {
          document.getElementById("background").getContext("2d").clearRect(0, 0, boxS * 100, boxS * 60);
          document.getElementById("foreground").getContext("2d").clearRect(0, 0, boxS * 100, boxS * 60);
          document.getElementById("water").getContext("2d").clearRect(0, 0, boxS * 100, boxS * 60);
        }
        for (var i in background) {
          for (var j in background[i]) {
            if (background[i][j]) {
              if (background[i][j] !== null) {
                selected = background[i][j];
                buildBlock(j, i, "bg", true, document.getElementById("background").getContext("2d"));
              }
            }
          }
        }
        for (var i in foreground) {
          for (var j in foreground[i]) {
            if (foreground[i][j]) {
              if (foreground[i][j] !== null) {
                selected = foreground[i][j];
                buildBlock(j, i, "fg", true, document.getElementById("foreground").getContext("2d"));
              }
            }
          }
        }
        for (var i in water) {
          for (var j in water[i]) {
            if (water[i][j]) {
              if (water[i][j] !== null) {
                selected = water[i][j];
                buildBlock(j, i, "water", true, document.getElementById("water").getContext("2d"));
              }
            }
          }
        }
        selected = startSel;
        switchBlock(selected);
      }
      function getSpread (type, grid, block,x,y) {
        switch (type) {
          case 5:
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:0,y:0};
            if (grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:1,y:0};
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2]) return {x:2,y:0};
            if (grid[0][1] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:3,y:0};
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[2][1]) return {x:4,y:0};
            if (grid[1][1] && grid[1][2] && grid[2][1]) return {x:5,y:0};
            if (grid[1][0] && grid[1][1] && grid[2][1]) return {x:6,y:0};
            if (grid[0][1] && grid[1][1] && grid[1][2]) return {x:7,y:0};
            if (grid[0][1] && grid[1][0] && grid[1][1]) return {x:0,y:1};
            if (grid[0][1] && grid[1][1] && grid[2][1]) return {x:1,y:1};
            if (grid[1][1] && grid[2][1]) return {x:2,y:1};
            if (grid[0][1] && grid[1][1]) return {x:3,y:1};
            if (grid[1][0] && grid[1][1] && grid[1][2]) return {x:5,y:1};
            if (grid[1][1] && grid[1][2]) return {x:6,y:1};
            if (grid[1][0] && grid[1][1]) return {x:7,y:1};
            if (grid[1][1]) return {x:4,y:1};
            return {x:0,y:0};
            break;
          case 4:
          case 9:
            var grid = [
              [0, 0, 0],
              [0, 1, 0],
              [0, 0, 0]
            ];
            layer = (isBackground(deconstructImage(block).clearName) ? background : foreground);
            if (block === "Gargoyle") layer = foreground;
            for (var k in grid) {
              var tempY = y-1 + parseInt(k);
              for (var d in grid[k]) {
                var tempX = x-1 + parseInt(d);
                if (layer[tempY]) {
                  if (layer[tempY][tempX] !== block && layer[tempY][tempX] !== null && layer[tempY][tempX]) {
                    if (data[deconstructImage(layer[tempY][tempX]).clearName].spread !== type) {
                      if (data[deconstructImage(layer[tempY][tempX]).clearName].col === 2) {
                        if (parseInt(k) === 2) grid[k][d] = 1;
                      } else if (data[deconstructImage(layer[tempY][tempX]).clearName].col === 1) grid[k][d] = 1;
                    }
                  }
                }
              }
            }
            if (grid[1][1] && grid[2][1]) return {x:3,y:0};
            if (grid[0][1] && grid[1][1]) return {x:1,y:0};
            if (grid[1][0] && grid[1][1]) return {x:0,y:0};
            if (grid[1][1] && grid[1][2]) return {x:2,y:0};
            if (type === 9) return {x:3,y:0};
            return {x:4,y:0};
            break;
          case 3:
          case 8:
            if (grid[1][0] && grid[1][1] && !grid[1][2]) return {x:2,y:0};
            if (grid[1][1] && grid[1][2] && !grid[1][0]) return {x:0,y:0};
            if (grid[1][0] && grid[1][1] && grid[1][2]) return {x:1,y:0};
            return {x:3,y:0};
          case 6:
            return {x:Math.floor(Math.random() * 4),y:0};
          case 7:
            if (grid[0][1] && grid[1][1] && !grid[2][1]) return {x:0,y:0};
            if (!grid[0][1] && grid[1][1] && grid[2][1]) return {x:2,y:0};
            if (grid[0][1] && grid[1][1] && grid[2][1]) return {x:1,y:0};
            return {x:3,y:0};
          case 2:
            if (grid[0][0] && grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][0] && grid[2][1] && grid[2][2]) return {x:0,y:0};
            if (grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][0] && grid[2][1] && grid[2][2]) return {x:5,y:1};
            if (grid[0][0] && grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][0] && grid[2][1] && grid[2][2]) return {x:6,y:1};
            if (grid[0][0] && grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1] && grid[2][2]) return {x:7,y:1};
            if (grid[0][0] && grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][0] && grid[2][1]) return {x:0,y:2};
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][0] && grid[2][1] && grid[2][2]) return {x:1,y:2};
            if (grid[0][0] && grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:2,y:2};
            if (grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1] && grid[2][2]) return {x:3,y:2};
            if (grid[0][0] && grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][0] && grid[2][1]) return {x:4,y:2};
            if (grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][0] && grid[2][1]) return {x:5,y:2};
            if (grid[0][0] && grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1] && grid[2][2]) return {x:6,y:2};
            if (grid[0][0] && grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:7,y:2};
            if (grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:0,y:3}; // 25
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1] && grid[2][0]) return {x:1,y:3}; // 26
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1] && grid[2][2]) return {x:2,y:3}; // 27
            if (grid[1][0] && grid[1][1] && grid[1][2] && grid[2][0] && grid[2][1] && grid[2][2]) return {x:1,y:0};
            if (grid[0][0] && grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2]) return {x:2,y:0};
            if (grid[0][1] && grid[0][2] && grid[1][1] && grid[1][2] && grid[2][1] && grid[2][2]) return {x:3,y:0};
            if (grid[0][0] && grid[0][1] && grid[1][0] && grid[1][1] && grid[2][0] && grid[2][1]) return {x:4,y:0};
            if (grid[0][1] && grid[0][2] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:0,y:4}; // 33
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:3,y:3}; // 28
            if (grid[0][1] && grid[1][1] && grid[1][2] && grid[2][1] && grid[2][2]) return {x:7,y:3}; // 33
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[2][0] && grid[2][1]) return {x:2,y:4}; // 35
            if (grid[0][0] && grid[0][1] && grid[1][0] && grid[1][1] && grid[2][1]) return {x:3,y:4}; // 36
            if (grid[2][1] && grid[2][2] && grid[1][0] && grid[1][1] && grid[1][2]) return {x:5,y:4}; // 38
            if (grid[2][0] && grid[2][1] && grid[1][0] && grid[1][1] && grid[1][2]) return {x:6,y:4}; // 39
            if (grid[0][1] && grid[0][2] && grid[1][0] && grid[1][1] && grid[1][2]) return {x:0,y:5}; // 41
            if (grid[0][0] && grid[0][1] && grid[1][0] && grid[1][1] && grid[1][2]) return {x:1,y:5}; // 42
            if (grid[0][1] && grid[1][1] && grid[1][2] && grid[2][1]) return {x:1,y:4}; // 34
            if (grid[0][1] && grid[1][0] && grid[1][1] && grid[2][1]) return {x:4,y:4}; // 37
            if (grid[2][1] && grid[1][0] && grid[1][1] && grid[1][2]) return {x:7,y:4}; // 40
            if (grid[1][1] && grid[1][0] && grid[1][2] && grid[0][1]) return {x:2,y:5}; // 43
            if (grid[1][1] && grid[1][2] && grid[2][1] && grid[2][2]) return {x:5,y:0};
            if (grid[1][0] && grid[1][1] && grid[2][0] && grid[2][1]) return {x:6,y:0};
            if (grid[1][1] && grid[1][2] && grid[0][1] && grid[0][2]) return {x:7,y:0};
            if (grid[1][0] && grid[1][1] && grid[0][0] && grid[0][1]) return {x:0,y:1};
            if (grid[0][1] && grid[1][1] && grid[2][1]) return {x:1,y:1};
            if (grid[0][1] && grid[1][1] && grid[1][2]) return {x:3,y:5}; // 44
            if (grid[0][1] && grid[1][0] && grid[1][1]) return {x:4,y:5}; // 45
            if (grid[1][1] && grid[1][2] && grid[2][1]) return {x:5,y:5}; // 46
            if (grid[1][0] && grid[1][1] && grid[2][1]) return {x:6,y:5}; // 47
            if (grid[1][0] && grid[1][1] && grid[1][2]) return {x:4,y:3}; // 29
            if (grid[1][1] && grid[1][2]) return {x:5,y:3};
            if (grid[1][0] && grid[1][1]) return {x:6,y:3};
            if (grid[0][1] && grid[1][1]) return {x:3,y:1};
            if (grid[1][1] && grid[2][1]) return {x:2,y:1};
            if (grid[1][1]) return {x:4,y:1}; // 13
            return {x:0,y:0};
          default:
            return {x:0,y:0};
        }
      }
    </script>
  </body>
</html>
