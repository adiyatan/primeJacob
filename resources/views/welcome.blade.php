<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Jacob is Online</title>
    <!--Style-->
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #000;
            color: #0F0;
        }

        .card {
            text-align: center;
        }

        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 18px;
            border-right: 2px solid #0F0;
        }

        p {
            font-size: 18px;
            margin-top: 10px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        a {
            text-decoration: none;
        }

        canvas {
            display: block;
        }

        .card {
            background-color: rgba(10, 10, 10, 0.7);
            border: rgba(50, 50, 50, 0.2) 1px solid;
            position: absolute;
            border-radius: 1.2rem;
            width: 60%;
            height: 70%;
            box-shadow: 20px 20px 50px rgba(0, 0, 0, 0.5);
            color: #f0f0f0;
            overflow-y: auto;
            -webkit-backdrop-filter: blur(3px);
            backdrop-filter: blur(3px);
            z-index: 12;
            user-select: none;
        }

        .texts {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .title_404 {
            font-size: 12vw;
        }

        .text_404 {
            font-size: 2vw;
        }

        .matrix {
            color: #0f0;
        }

        .text_stroke {
            color: transparent;
            -webkit-text-stroke: 3px #0f0;
        }

        .close_btn {
            position: absolute;
            right: 0;
        }

        #search_box {
            width: 60%;
            border-radius: 10px;
            box-shadow: none;
            padding: .7rem .8rem;
            margin: 10px 0;
            font-size: 1vw;
            background-color: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: rgba(255, 255, 255, 0.8);
        }

        @media (max-width: 768px) {
            .card {
                width: 85%;
            }

            .title_404 {
                font-size: 100px;
            }

            .text_404 {
                font-size: 16px;
            }

            #search_box {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <!--Card-->
    <div class="card">
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <pre>
                
            ('-.                           .-. .-')   
            ( OO ).-.                       \  ( OO )  
      ,--.  / . --. /   .-----.  .-'),-----. ;-----.\  
  .-')| ,|  | \-.  \   '  .--./ ( OO'  .-.  '| .-.  |  
 ( OO |(_|.-'-'  |  |  |  |('-. /   |  | |  || '-' /_) 
 | `-'|  | \| |_.'  | /_) |OO  )\_) |  |\|  || .-. `.  
 ,--. |  |  |  .-.  | ||  |`-'|   \ |  | |  || |  \  | 
 |  '-'  /  |  | |  |(_'  '--'\    `'  '-'  '| '--'  / 
  `-----'   `--' `--'   `-----'      `-----' `------'   
        </pre>
        <p>at your service</p>
        <p>-Masterpiece By Adiyatan-</p>
    </div>
    <!--Matrix-->
    <canvas id="matrix_"></canvas>

    <!-- script -->
    <script>
        window.onload = Load;
        window.onresize = getWindowSize;

        const canvasTag = document.getElementById("matrix_");

        function getWindowSize() {
            canvasTag.height = window.innerHeight;
            canvasTag.width = window.innerWidth;
        }

        function Load() {
            getWindowSize();

            const letter_size = 16; //letters size
            const columnsNumber = canvasTag.width / letter_size; //Get the number of columns

            let letters = [];
            for (let i = 0; i < columnsNumber; i++) {
                letters[i] = 1;
            }
            let context = canvasTag.getContext('2d'); //canvas

            function canvasCreator() {
                context.fillStyle = "rgba(0,0,0,0.08)"; //canvas background
                context.fillRect(0, 0, canvasTag.width, canvasTag.height);

                context.fillStyle = "#0f0"; //letters color
                context.font = letter_size + "px arial"; //letters font
                let text;
                for (let i = 0; i < letters.length; i++) {
                    text = Create_Word();
                    context.fillText(text, i * letter_size, letters[i] * letter_size);

                    if (letters[i] * letter_size > canvasTag.height && Math.random() > 0.975) {
                        letters[i] = 0;
                    }
                    letters[i]++;
                }
            }

            function Create_Word() {
                let numberText = Math.floor((Math.random() * 94) + 33); //Create char from code
                return String.fromCharCode(parseInt(numberText)); //return char code
            }

            setInterval(canvasCreator, 80); //Timer
        }
    </script>
    <script>
        var VanillaTilt = (function() {
            "use strict";
            class t {
                constructor(e, i = {}) {
                    if (!(e instanceof Node)) throw "Can't initialize VanillaTilt because " + e +
                        " is not a Node.";
                    this.width = null, this.height = null, this.clientWidth = null, this.clientHeight = null,
                        this.left = null, this.top = null, this.gammazero = null, this.betazero = null, this
                        .lastgammazero = null, this.lastbetazero = null, this.transitionTimeout = null, this
                        .updateCall = null, this.event = null, this.updateBind = this.update.bind(this), this
                        .resetBind = this.reset.bind(this), this.element = e, this.settings = this
                        .extendSettings(i), this.reverse = this.settings.reverse ? -1 : 1, this.glare = t
                        .isSettingTrue(this.settings.glare), this.glarePrerender = t.isSettingTrue(this
                            .settings["glare-prerender"]), this.fullPageListening = t.isSettingTrue(this
                            .settings["full-page-listening"]), this.gyroscope = t.isSettingTrue(this.settings
                            .gyroscope), this.gyroscopeSamples = this.settings.gyroscopeSamples, this
                        .elementListener = this.getElementListener(), this.glare && this.prepareGlare(), this
                        .fullPageListening && this.updateClientSize(), this.addEventListeners(), this.reset(),
                        this.updateInitialPosition();
                }
                static isSettingTrue(t) {
                    return "" === t || !0 === t || 1 === t;
                }
                getElementListener() {
                    if (this.fullPageListening) return window.document;
                    if ("string" == typeof this.settings["mouse-event-element"]) {
                        const t = document.querySelector(this.settings["mouse-event-element"]);
                        if (t) return t;
                    }
                    return this.settings["mouse-event-element"] instanceof Node ? this.settings[
                        "mouse-event-element"] : this.element;
                }
                addEventListeners() {
                    this.onMouseEnterBind = this.onMouseEnter.bind(this), this.onMouseMoveBind = this
                        .onMouseMove.bind(this), this.onMouseLeaveBind = this.onMouseLeave.bind(this), this
                        .onWindowResizeBind = this.onWindowResize.bind(this), this.onDeviceOrientationBind =
                        this.onDeviceOrientation.bind(this), this.elementListener.addEventListener("mouseenter",
                            this.onMouseEnterBind), this.elementListener.addEventListener("mouseleave", this
                            .onMouseLeaveBind), this.elementListener.addEventListener("mousemove", this
                            .onMouseMoveBind), (this.glare || this.fullPageListening) && window
                        .addEventListener("resize", this.onWindowResizeBind), this.gyroscope && window
                        .addEventListener("deviceorientation", this.onDeviceOrientationBind);
                }
                removeEventListeners() {
                    this.elementListener.removeEventListener("mouseenter", this.onMouseEnterBind), this
                        .elementListener.removeEventListener("mouseleave", this.onMouseLeaveBind), this
                        .elementListener.removeEventListener("mousemove", this.onMouseMoveBind), this
                        .gyroscope && window.removeEventListener("deviceorientation", this
                            .onDeviceOrientationBind), (this.glare || this.fullPageListening) && window
                        .removeEventListener("resize", this.onWindowResizeBind);
                }
                destroy() {
                    clearTimeout(this.transitionTimeout), null !== this.updateCall && cancelAnimationFrame(this
                            .updateCall), this.reset(), this.removeEventListeners(), this.element.vanillaTilt =
                        null, delete this.element.vanillaTilt, this.element = null;
                }
                onDeviceOrientation(t) {
                    if (null === t.gamma || null === t.beta) return;
                    this.updateElementPosition(), this.gyroscopeSamples > 0 && (this.lastgammazero = this
                        .gammazero, this.lastbetazero = this.betazero, null === this.gammazero ? (this
                            .gammazero = t.gamma, this.betazero = t.beta) : (this.gammazero = (t.gamma +
                            this.lastgammazero) / 2, this.betazero = (t.beta + this.lastbetazero) / 2), this
                        .gyroscopeSamples -= 1);
                    const e = this.settings.gyroscopeMaxAngleX - this.settings.gyroscopeMinAngleX,
                        i = this.settings.gyroscopeMaxAngleY - this.settings.gyroscopeMinAngleY,
                        s = e / this.width,
                        n = i / this.height,
                        l = (t.gamma - (this.settings.gyroscopeMinAngleX + this.gammazero)) / s,
                        a = (t.beta - (this.settings.gyroscopeMinAngleY + this.betazero)) / n;
                    null !== this.updateCall && cancelAnimationFrame(this.updateCall), this.event = {
                        clientX: l + this.left,
                        clientY: a + this.top
                    }, this.updateCall = requestAnimationFrame(this.updateBind);
                }
                onMouseEnter() {
                    this.updateElementPosition(), this.element.style.willChange = "transform", this
                        .setTransition();
                }
                onMouseMove(t) {
                    null !== this.updateCall && cancelAnimationFrame(this.updateCall), this.event = t, this
                        .updateCall = requestAnimationFrame(this.updateBind);
                }
                onMouseLeave() {
                    this.setTransition(), this.settings.reset && requestAnimationFrame(this.resetBind);
                }
                reset() {
                    this.event = {
                        clientX: this.left + this.width / 2,
                        clientY: this.top + this.height / 2
                    }, this.element && this.element.style && (this.element.style.transform =
                        `perspective(${this.settings.perspective}px) ` +
                        "rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)"), this.resetGlare();
                }
                resetGlare() {
                    this.glare && (this.glareElement.style.transform = "rotate(180deg) translate(-50%, -50%)",
                        this.glareElement.style.opacity = "0");
                }
                updateInitialPosition() {
                    if (0 === this.settings.startX && 0 === this.settings.startY) return;
                    this.onMouseEnter(), this.fullPageListening ? this.event = {
                        clientX: (this.settings.startX + this.settings.max) / (2 * this.settings.max) * this
                            .clientWidth,
                        clientY: (this.settings.startY + this.settings.max) / (2 * this.settings.max) * this
                            .clientHeight
                    } : this.event = {
                        clientX: this.left + (this.settings.startX + this.settings.max) / (2 * this.settings
                            .max) * this.width,
                        clientY: this.top + (this.settings.startY + this.settings.max) / (2 * this.settings
                            .max) * this.height
                    };
                    let t = this.settings.scale;
                    this.settings.scale = 1, this.update(), this.settings.scale = t, this.resetGlare();
                }
                getValues() {
                    let t, e;
                    return this.fullPageListening ? (t = this.event.clientX / this.clientWidth, e = this.event
                            .clientY / this.clientHeight) : (t = (this.event.clientX - this.left) / this.width,
                            e = (this.event.clientY - this.top) / this.height), t = Math.min(Math.max(t, 0), 1),
                        e = Math.min(Math.max(e, 0), 1), {
                            tiltX: (this.reverse * (this.settings.max - t * this.settings.max * 2)).toFixed(2),
                            tiltY: (this.reverse * (e * this.settings.max * 2 - this.settings.max)).toFixed(2),
                            percentageX: 100 * t,
                            percentageY: 100 * e,
                            angle: Math.atan2(this.event.clientX - (this.left + this.width / 2), -(this.event
                                .clientY - (this.top + this.height / 2))) * (180 / Math.PI)
                        };
                }
                updateElementPosition() {
                    let t = this.element.getBoundingClientRect();
                    this.width = this.element.offsetWidth, this.height = this.element.offsetHeight, this.left =
                        t.left, this.top = t.top;
                }
                update() {
                    let t = this.getValues();
                    this.element.style.transform = "perspective(" + this.settings.perspective + "px) rotateX(" +
                        ("x" === this.settings.axis ? 0 : t.tiltY) + "deg) rotateY(" + ("y" === this.settings
                            .axis ? 0 : t.tiltX) + "deg) scale3d(" + this.settings.scale + ", " + this.settings
                        .scale + ", " + this.settings.scale + ")", this.glare && (this.glareElement.style
                            .transform = `rotate(${t.angle}deg) translate(-50%, -50%)`, this.glareElement.style
                            .opacity = `${t.percentageY * this.settings["max-glare"] / 100}`), this.element
                        .dispatchEvent(new CustomEvent("tiltChange", {
                            detail: t
                        })), this.updateCall = null;
                }
                prepareGlare() {
                    if (!this.glarePrerender) {
                        const t = document.createElement("div");
                        t.classList.add("js-tilt-glare");
                        const e = document.createElement("div");
                        e.classList.add("js-tilt-glare-inner"), t.appendChild(e), this.element.appendChild(t);
                    }
                    this.glareElementWrapper = this.element.querySelector(".js-tilt-glare"), this.glareElement =
                        this.element.querySelector(".js-tilt-glare-inner"), this.glarePrerender || (Object
                            .assign(this.glareElementWrapper.style, {
                                position: "absolute",
                                top: "0",
                                left: "0",
                                width: "100%",
                                height: "100%",
                                overflow: "hidden",
                                "pointer-events": "none"
                            }), Object.assign(this.glareElement.style, {
                                position: "absolute",
                                top: "50%",
                                left: "50%",
                                "pointer-events": "none",
                                "background-image": "linear-gradient(0deg, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%)",
                                width: `${2 * this.element.offsetWidth}px`,
                                height: `${2 * this.element.offsetWidth}px`,
                                transform: "rotate(180deg) translate(-50%, -50%)",
                                "transform-origin": "0% 0%",
                                opacity: "0"
                            }));
                }
                updateGlareSize() {
                    this.glare && Object.assign(this.glareElement.style, {
                        width: `${2 * this.element.offsetWidth}`,
                        height: `${2 * this.element.offsetWidth}`
                    });
                }
                updateClientSize() {
                    this.clientWidth = window.innerWidth || document.documentElement.clientWidth || document
                        .body.clientWidth, this.clientHeight = window.innerHeight || document.documentElement
                        .clientHeight || document.body.clientHeight;
                }
                onWindowResize() {
                    this.updateGlareSize(), this.updateClientSize();
                }
                setTransition() {
                    clearTimeout(this.transitionTimeout), this.element.style.transition = this.settings.speed +
                        "ms " + this.settings.easing, this.glare && (this.glareElement.style.transition =
                            `opacity ${this.settings.speed}ms ${this.settings.easing}`), this
                        .transitionTimeout = setTimeout(() => {
                            this.element.style.transition = "", this.glare && (this.glareElement.style
                                .transition = "")
                        }, this.settings.speed);
                }
                extendSettings(t) {
                    let e = {
                            reverse: !1,
                            max: 15,
                            startX: 0,
                            startY: 0,
                            perspective: 1e3,
                            easing: "cubic-bezier(.03,.98,.52,.99)",
                            scale: 1,
                            speed: 300,
                            transition: !0,
                            axis: null,
                            glare: !1,
                            "max-glare": 1,
                            "glare-prerender": !1,
                            "full-page-listening": !1,
                            "mouse-event-element": null,
                            reset: !0,
                            gyroscope: !0,
                            gyroscopeMinAngleX: -45,
                            gyroscopeMaxAngleX: 45,
                            gyroscopeMinAngleY: -45,
                            gyroscopeMaxAngleY: 45,
                            gyroscopeSamples: 10
                        },
                        i = {};
                    for (var s in e)
                        if (s in t) i[s] = t[s];
                        else if (this.element.hasAttribute("data-tilt-" + s)) {
                        let t = this.element.getAttribute("data-tilt-" + s);
                        try {
                            i[s] = JSON.parse(t)
                        } catch (e) {
                            i[s] = t
                        }
                    } else i[s] = e[s];
                    return i;
                }
                static init(e, i) {
                    e instanceof Node && (e = [e]), e instanceof NodeList && (e = [].slice.call(e)),
                        e instanceof Array && e.forEach(e => {
                            "vanillaTilt" in e || (e.vanillaTilt = new t(e, i))
                        });
                }
            }
            return "undefined" != typeof document && (window.VanillaTilt = t, t.init(document.querySelectorAll(
                "[data-tilt]"))), t;
        })();
    </script>
</body>

</html>
