// JavaScript Document


        var video = document.getElementById("Video1");
        var vLength;
        var pgFlag = ""; // used for progress tracking
        if (video.canPlayType) {   // tests that we have HTML5 video support

          //  video button helper functions
          //  play video
          function vidplay(evt) {
            if (video.src == "") {  // inital source load
              getVideo();
            }
            if (video.paused) {   // play the file, and display pause symbol
              video.play();
            } else {              // pause the file, and display play symbol  
              video.pause();
            }
          }
          
          //  load video file from input field
          function getVideo() {
            var fileURL = document.getElementById("videoFile").value; // get input field                    
            if (fileURL != "") {
              video.src = fileURL;
              video.load();  // if HTML source element is used
			  document.getElementById('textarea').textContent = "0";
              document.getElementById("play").click();  // start play
			 		 
			  
            } else {
              errMessage("Enter a valid video URL");  // fail silently
            }
          }


          //  button helper functions 
          //  skip forward, backward, or restart
          function setTime(tValue) {
            //  if no video is loaded, this throws an exception 
            try {
              if (tValue == 0) {
                video.currentTime = tValue;
              }
              else {
                video.currentTime += tValue;
              }

            } catch (err) {
              // errMessage(err) // show exception
              errMessage("Video content might not be loaded");
            }
          }

          // change volume based on incoming value 
          function setVol(value) {
            var vol = video.volume;
            vol += value;
            //  test for range 0 - 1 to avoid exceptions
            if (vol >= 0 && vol <= 1) {
              // if valid value, use it
              video.volume = vol;
            } else {
              // otherwise substitute a 0 or 1
              video.volume = (vol < 0) ? 0 : 1;
            }
          }
          //  button events               
          //  Play
          document.getElementById("play").addEventListener("click", vidplay, false);
          //  Restart
          document.getElementById("restart").addEventListener("click", function () {
            setTime(0);
          }, false);
          //  Skip backward 10 seconds
          document.getElementById("rew").addEventListener("click", function () {
            setTime(-10);
          }, false);
          //  Skip forward 10 seconds
          document.getElementById("fwd").addEventListener("click", function () {
            setTime(10);
          }, false);
          //  set src == latest video file URL
          document.getElementById("loadVideo").addEventListener("click", getVideo, false);

          // volume buttons
          document.getElementById("volDn").addEventListener("click", function () {
            setVol(-.1); // down by 10%
          }, false);
          document.getElementById("volUp").addEventListener("click", function () {
            setVol(.1);  // up by 10%
          }, false);

          // playback speed buttons
          document.getElementById("slower").addEventListener("click", function () {
            video.playbackRate -= .25;
          }, false);
          document.getElementById("faster").addEventListener("click", function () {
            video.playbackRate += .25;
          }, false);
          document.getElementById("normal").addEventListener("click", function () {
            video.playbackRate = 1;
          }, false);
          document.getElementById("mute").addEventListener("click", function (evt) {
            if (video.muted) {
              video.muted = false;
            } else {
              video.muted = true;
            }
          }, false);

          //  any video error will fail with message 
          video.addEventListener("error", function (err) {
            errMessage(err);
          }, true);
          // content has loaded, display buttons and set up events
          video.addEventListener("canplay", function () {
            document.getElementById("buttonbar").style.display = "block";
          }, false);

          //  display video duration when available
          video.addEventListener("loadedmetadata", function () {
            vLength = video.duration.toFixed(1);
            document.getElementById("vLen").textContent = vLength; // global variable
          }, false);

          //  display the current and remaining times
          video.addEventListener("timeupdate", function () {
            //  Current time  
            var vTime = video.currentTime;
            document.getElementById("curTime").textContent = vTime.toFixed(1);
            document.getElementById("vRemaining").textContent = (vLength - vTime).toFixed(1);
          }, false);
          //  paused and playing events to control buttons
          video.addEventListener("pause", function () {
            document.getElementById("play").innerHTML = "<img alt='Play button' src='play.png' />";
			/* document.getElementById("loadVideo").innerHTML = "<img alt='Load Video' src='load.png' />";*/
			document.getElementById('statusbut').style.backgroundColor = '#4CAF50';
			document.getElementById('statusbut').textContent = "Start";
			//document.getElementById('textarea').textContent = "0";
			//document.getElementById('submit').disabled = false;
			//document.getElementById('submit').style.backgroundColor = '#4CAF50';
			
			
          }, false);

          video.addEventListener("playing", function () {
            document.getElementById("play").innerHTML = "<img alt='Play button' src='pause.png' />";
			/*document.getElementById("loadVideo").innerHTML = "<img alt='Load Video' src='load.png' />";*/
			document.getElementById('statusbut').style.backgroundColor = '#ff0000';
			document.getElementById('statusbut').textContent = "Stop";
			//document.getElementById('submit').disabled = true;
			//document.getElementById('submit').style.backgroundColor = '#eee';
			
          }, false); 
		  
		  
		  

          video.addEventListener("volumechange", function () {
            if (video.muted) {
              // if muted, show mute image
              document.getElementById("mute").innerHTML = "<img alt='volume off button' src='vol2.png' />";
            } else {
              // if not muted, show not muted image
              document.getElementById("mute").innerHTML = "<img alt='volume on button' src='mute2.png' />";
            }
          }, false);
          //  Download and playback status events.
          video.addEventListener("loadstart", function () {
            document.getElementById("ls").textContent = "Started";
          }, false);
          video.addEventListener("loadeddata", function () {
            document.getElementById("ld").textContent = "Data was loaded";
          }, false);

          video.addEventListener("ended", function () {
            document.getElementById("ndd").textContent = "Playback ended";
          }, false);

          video.addEventListener("emptied", function () {
            document.getElementById("mt").textContent = "Video reset";
          }, false);

          video.addEventListener("stalled", function () {
            document.getElementById("stall").textContent = "Download was stalled";
          }, false);
          video.addEventListener("waiting", function () {
            document.getElementById("waiting").textContent = "Player waited for content";
          }, false);
          video.addEventListener("progress", function () {
            pgFlag += "+";
            if (pgFlag.length > 10) {
              pgFlag = "+";
            }
            document.getElementById("pg").textContent = pgFlag;

          }, false);
          video.addEventListener("durationchange", function () {
            document.getElementById("dc").textContent = "Duration has changed";
          }, false);
          video.addEventListener("canplaythrough", function () {
            document.getElementById("cpt").textContent = "Ready to play whole video";
          }, false);
        } else {
          errMessage("HTML5 Video is required for this example");
          // end of runtime
        }
        //  display an error message 
        function errMessage(msg) {
          // displays an error message for 5 seconds then clears it
          document.getElementById("errorMsg").textContent = msg;
          setTimeout("document.getElementById('errorMsg').textContent=''", 5000);
        }
    