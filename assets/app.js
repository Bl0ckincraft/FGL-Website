import './styles/app.scss'

import './scripts/loading'
import './scripts/theme'
import './scripts/notifications'

// Menu code
document.addEventListener("DOMContentLoaded", () => {
    let menuSwitch = document.getElementsByClassName("menu-switch");
    let menu = document.getElementById("menu");
    let menuModal = document.getElementById("menu-modal");

    let switchMenu = (action) => {
        if (menu.classList.contains("enabled") && (action == null || typeof action == "undefined" || action === "close")) {
            menu.classList.remove("enabled")
        } else if (!menu.classList.contains("enabled") && (action == null || typeof action == "undefined" || action === "open")) {
            menu.classList.add("enabled")
        }
    }

    for (let s of menuSwitch) {
        s.addEventListener("click", () => {
            switchMenu();
        });
    }

    menuModal.addEventListener("click", () => {
        switchMenu("close");
    })
})

// Custom Background Code
document.addEventListener("DOMContentLoaded", function() {
    const background = document.getElementById("background")
    background.setAttribute("hovered", "")
    background.removeAttribute("hovered")

    if (background != null && typeof background != "undefined") {
        function calcLength() {
            return Math.max(75, background.offsetWidth * 0.05);
        }

        function calcWidth() {
            return calcLength() / 7.0;
        }

        const cssLength = "max(75px, calc(100vw * 0.05))";
        const cssWidth = "calc(var(--bg--length) / 7.0)";
        let nodes = [];

        const backgroundContainer = document.getElementById("background")
        backgroundContainer.style.setProperty("--bg--length", cssLength)
        backgroundContainer.style.setProperty("--bg--width", cssWidth)
        backgroundContainer.style.setProperty("--bg--shadow-width", "max(1px, calc(100vw * 0.001))")

        let nodeId = 0;

        const pattern = [
            [[1, 1], [1, 1], [1, 1], [1, 0]],
            [[0, 1], [1, 1], [1, 0], [1, 1]],
            [[1, 1], [0, 1], [1, 1], [0, 1]],
            [[1, 0], [1, 0], [0, 1], [1, 1]]
        ]

        let lightId = 0;
        let lights = [];

        function getNodeByIJ(i, j) {
            for (let index in nodes) {
                let node = nodes[index]
                if (node.i === i && node.j === j) return node;
            }
        }

        function validDestination(node) {
            return typeof node !== "undefined" && node.i <= Math.ceil(backgroundContainer.offsetWidth / (calcLength() + calcWidth())) && node.j <= Math.ceil(backgroundContainer.offsetHeight / (calcLength() + calcWidth()))
        }

        function getRandomNode(inNodes) {
            return inNodes[Math.floor(Math.random() * inNodes.length)]
        }

        function getValidRandomNode(inNodes) {
            let node;

            do {
                node = getRandomNode(inNodes)
            } while (!validDestination(node))

            return node;
        }

        function getNextNode(currentNode, previousNode) {
            let availlableNodes = [];
            let preferedAvaillableNodes = [];
            let topNode = getNodeByIJ(currentNode.i, currentNode.j - 1)
            let bottomNode = getNodeByIJ(currentNode.i, currentNode.j + 1)
            let leftNode = getNodeByIJ(currentNode.i - 1, currentNode.j)
            let rightNode = getNodeByIJ(currentNode.i + 1, currentNode.j)

            if (typeof topNode != "undefined" && topNode.down) {
                availlableNodes.push(topNode)
                if (typeof previousNode == "undefined" || previousNode.id !== topNode.id) {
                    preferedAvaillableNodes.push(topNode)
                }
            }

            if (typeof bottomNode != "undefined" && currentNode.down) {
                availlableNodes.push(bottomNode)
                if (typeof previousNode == "undefined" || previousNode.id !== bottomNode.id) {
                    preferedAvaillableNodes.push(bottomNode)
                }
            }

            if (typeof leftNode != "undefined" && leftNode.right) {
                availlableNodes.push(leftNode)
                if (typeof previousNode == "undefined" || previousNode.id !== leftNode.id) {
                    preferedAvaillableNodes.push(leftNode)
                }
            }

            if (typeof rightNode != "undefined" && currentNode.right) {
                availlableNodes.push(rightNode)
                if (typeof previousNode == "undefined" || previousNode.id !== rightNode.id) {
                    preferedAvaillableNodes.push(rightNode)
                }
            }

            if (availlableNodes.length === 0) return currentNode;
            else if (preferedAvaillableNodes.length === 0) return getRandomNode(availlableNodes);
            else return getRandomNode(preferedAvaillableNodes)
        }

        function addLight() {
            if (nodes.length === 0) return;
            let start = getValidRandomNode(nodes);
            let from = getNextNode(start, start)
            let target = getNextNode(from, start);

            lights.push({
                "prev": start,
                "from": from,
                "target": target,
                "progress": 101 + Math.floor(Math.random() * 100),
                "id": lightId,
            })

            lightId ++;
        }

        function addLights(amount) {
            for (let i = 0; i < amount; i++) {
                addLight()
            }
        }

        function removeLight(id) {
            let newLights = []
            for (let i in lights) {
                let light = lights[i]
                if (light.id === id) {
                    let lightElement = document.getElementById("light-" + id)
                    if (lightElement != null && typeof lightElement != "undefined") {
                        lightElement.remove()
                    }
                } else {
                    newLights.push(light)
                }
            }

            lights = newLights;
        }

        function clearLights() {
            for (let i in lights) {
                let light = lights[i]
                let lightElement = document.getElementById("light-" + light.id)
                if (lightElement != null && typeof lightElement != "undefined") {
                    lightElement.remove()
                }
            }

            lights = []
        }

        let lastMaxI = 0
        let lastMaxJ = 0
        function generateBackground() {
            let length = calcLength();
            let width = calcWidth();

            let maxI = Math.ceil(backgroundContainer.offsetWidth / (length + width))
            let maxJ = Math.ceil(backgroundContainer.offsetHeight / (length + width))

            for (let i = -1; i < maxI; i++) {
                for (let j = -1; j < maxJ; j++) {
                    let n = getNodeByIJ(i, j);
                    if (n != null && typeof n != "undefined") continue;

                    let node = {
                        "id": nodeId,
                        "i": i,
                        "j": j,
                        "right": pattern[(j + pattern.length) % pattern.length][(i + pattern[0].length) % pattern[0].length][0] == 1,
                        "down": pattern[(j + pattern.length) % pattern.length][(i + pattern[0].length) % pattern[0].length][1] == 1
                    };

                    nodes.push(node)
                    let shadow = "";
                    if (node.down) shadow += "calc(-1 * var(--bg--shadow-width)) 0 0 var(--bg--shadow-color), "; // Left shadow
                    if (node.right) shadow += "0 calc(-1 * var(--bg--shadow-width)) 0 var(--bg--shadow-color), "; // Top shadow
                    if (pattern[(j + pattern.length + 1) % pattern.length][(i + pattern[0].length) % pattern[0].length][0] === 1) shadow += "0 var(--bg--shadow-width) 0 var(--bg--shadow-color), "; // Bottom shadow
                    if (pattern[(j + pattern.length) % pattern.length][(i + pattern[0].length + 1) % pattern[0].length][1] === 1) shadow += "var(--bg--shadow-width) 0 0 var(--bg--shadow-color), "; // Right shadow

                    if (shadow.endsWith(", ")) shadow = shadow.substring(0, shadow.length - 2)

                    backgroundContainer.insertAdjacentHTML("beforeend", "<div id=\"box-" + node["id"] + "\" style=\"--bg--shadow:" + shadow + "; --bg--i: " + i + "; --bg--j: " + j + "\" class=\"box\"></div>")

                    if (!node.right) {
                        backgroundContainer.insertAdjacentHTML("beforeend", "<div id=\"right-filled-path-" + node["id"] + "\" style=\"--bg--i: " + i + "; --bg--j: " + j + "\" class=\"filled-path\" side=\"right\"></div>")
                    }

                    if (!node.down) {
                        backgroundContainer.insertAdjacentHTML("beforeend", "<div id=\"down-filled-path-" + node["id"] + "\" style=\"--bg--i: " + i + "; --bg--j: " + j + "\" class=\"filled-path\" side=\"down\"></div>")
                    }

                    nodeId++;
                }
            }

            if (lastMaxI !== maxI || lastMaxJ !== maxJ) {
                lastMaxI = maxI
                lastMaxJ = maxJ
                clearLights()
                addLights(5)
            }
        }

        setInterval(() => {
            for (let index in lights) {
                let light = lights[index]
                if (typeof light.elem == "undefined" || light.elem == null) {
                    light.elem = document.createElement("div")
                    light.elem.id = "light-" + light.id
                    light.elem.classList.add("light")
                    backgroundContainer.insertAdjacentElement("beforeend", light.elem)

                    light.prevElem = document.createElement("div")
                    light.prevElem.classList.add("prev-light-part")
                    light.elem.insertAdjacentElement("beforeend", light.prevElem)

                    light.currentElem = document.createElement("div")
                    light.currentElem.classList.add("current-light-part")
                    light.elem.insertAdjacentElement("beforeend", light.currentElem)
                }


                light.progress += 1
                if (light.progress > 100) {
                    light.prev = light.from;
                    light.from = light.target;
                    light.target = getNextNode(light.from, light.prev)
                    light.progress -= 100

                    light.elem.style.setProperty("--bg--prev-i", light.prev.i)
                    light.elem.style.setProperty("--bg--prev-j", light.prev.j)
                    light.elem.style.setProperty("--bg--from-i", light.from.i)
                    light.elem.style.setProperty("--bg--from-j", light.from.j)
                    light.elem.style.setProperty("--bg--target-i", light.target.i)
                    light.elem.style.setProperty("--bg--target-j", light.target.j)
                }

                light.elem.style.setProperty("--bg--progress", light.progress)
            }
        }, 20)

        generateBackground()
        window.addEventListener("resize", (e) => {
            generateBackground()
        })
    }
})

// Card
document.addEventListener("DOMContentLoaded", () => {
    document.getElementsByClassName("card")

    for (let e of document.getElementsByClassName("card")) {
        e.addEventListener("click", () => {
            if (e.classList.contains("flipped")) {
                e.classList.remove("flipped")
            } else {
                e.classList.add("flipped")
            }
        })
    }
})

// Waves
document.addEventListener("DOMContentLoaded", () => {
    let styleElement = document.getElementsByTagName("html").item(0)

    let applyParams = () => {
        let waveHeight = Math.min(170, styleElement.offsetWidth / 6)
        let waveWidth = 800 / 88.7 * waveHeight;
        let waveTranslateX = -Math.round(styleElement.offsetWidth / waveWidth + 1) * waveWidth

        styleElement.style.setProperty("--wave-height", waveHeight + "px")
        styleElement.style.setProperty("--wave-width", waveWidth + "px")
        styleElement.style.setProperty("--wave-translate-x", waveTranslateX + "px")
    }

    applyParams()
    document.addEventListener("resize", () => {
        applyParams()
    })
})