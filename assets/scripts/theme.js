let themeTransitionEnabled = false;
const themeTransitionDuration = 1000;
const defaultMainTheme = "dark";
const defaultAccentTheme = "pink";

let currentValues = {}
let vars = []
let lastChange = 0;
let animationHandle;

let styleElement;

let numberType = (from, to, progress) => {
    return {
        "value": (to - from) * progress + from,
    };
}

let colorType = (from, to, progress) => {
    let value = [numberType(from[0], to[0], progress).value, numberType(from[1], to[1], progress).value, numberType(from[2], to[2], progress).value];
    if (from.length === 4) {
        value.push(numberType(from[3], to[3], progress).value)
    }

    let variants = {
        "rgb": `${value[0]}, ${value[1]}, ${value[2]}`,
    }

    if (from.length === 4) {
        variants["alpha"] = `${value[3]}`
    }

    return {
        "value": value,
        "cssValue": value.length === 3 ? `rgb(${value[0]}, ${value[1]}, ${value[2]})` : `rgba(${value[0]}, ${value[1]}, ${value[2]}, ${value[3]})`,
        "variants": variants
    }
}

const settings = {
    mainColor: {
        type: colorType,
        predicates: {
            ["dark"]: {
                any: {
                    default: [26, 26, 32],
                    variant: [8, 8, 8]
                }
            },
            ["light"]: {
                any: {
                    default: [245, 245, 245],
                    variant: [215, 215, 215]
                }
            }
        }
    },
    oppositeMainColor: {
        type: colorType,
        predicates: {
            ["dark"]: {
                any: {
                    default: [245, 245, 245],
                    variant: [215, 215, 215]
                }
            },
            ["light"]: {
                any: {
                    default: [26, 26, 32],
                    variant: [8, 8, 8]
                }
            }
        }
    },
    textColor: {
        type: colorType,
        predicates: {
            ["dark"]: {
                any: {
                    default: [255, 255, 255],
                    variant: [192, 192, 192]
                }
            },
            ["light"]: {
                any: {
                    default: [30, 30, 30],
                    variant: [128, 128, 128]
                }
            }
        }
    },
    accentColor: {
        type: colorType,
        predicates: {
            ["dark"]: {
                ["blue"]: {
                    default: [0, 157, 255],
                    variant: [0, 75, 128]
                },
                ["pink"]: {
                    default: [255, 0, 217],
                    variant: [128, 0, 108]
                },
                ["yellow"]: {
                    default: [225, 225, 0],
                    variant: [128, 128, 0]
                },
                ["green"]: {
                    default: [0, 255, 0],
                    variant: [0, 128, 0]
                }
            },
            ["light"]: {
                ["blue"]: {
                    default: [173, 216, 255],
                    variant: [110, 144, 212]
                },
                ["pink"]: {
                    default: [255, 173, 217],
                    variant: [212, 110, 144]
                },
                ["yellow"]: {
                    default: [255, 255, 0],
                    variant: [200, 200, 0]
                },
                ["green"]: {
                    default: [144, 212, 110],
                    variant: [110, 144, 70]
                }
            }
        }
    },
    iconBrightness: {
        type: numberType,
        predicates: {
            ["dark"]: {
                any: {
                    default: 1
                }
            },
            ["light"]: {
                any: {
                    default: .9
                }
            }
        }
    },
    iconHueRotate: {
        type: (from, to, progress) => {
            let r = numberType(from, to, progress);
            return {
                "value": r.value,
                "cssValue": r.value + "deg"
            }
        },
        predicates: {
            any: {
                ["blue"]: {
                    default: 170
                },
                ["pink"]: {
                    default: 250
                },
                ["yellow"]: {
                    default: 20
                },
                ["green"]: {
                    default: 80
                }
            }
        }
    },
    usefulColor: {
        type: colorType,
        predicates: {
            "any": {
                any: {
                    darkThemeYellow: [249, 178, 52],
                    darkThemeGreen: [62, 205, 94],
                    darkThemeBlue: [2, 149, 228],
                    darkThemePurple: [149, 42, 255],
                    darkThemePink: [205, 62, 148],
                    darkThemeRed: [199, 48, 48],
                    lightThemeYellow: [251, 216, 124],
                    lightThemeGreen: [110, 250, 139],
                    lightThemeBlue: [114, 184, 246],
                    lightThemePurple: [183, 157, 255],
                    lightThemePink: [241, 120, 197],
                    lightThemeRed: [240, 93, 93]
                }
            },
            ["dark"]: {
                any: {
                    yellow: [249, 178, 52],
                    green: [62, 205, 94],
                    blue: [2, 149, 228],
                    purple: [149, 42, 255],
                    pink: [205, 62, 148],
                    red: [199, 48, 48]
                }
            },
            ["light"]: {
                any: {
                    yellow: [251, 216, 124],
                    green: [110, 250, 139],
                    blue: [114, 184, 246],
                    purple: [183, 157, 255],
                    pink: [241, 120, 197],
                    red: [240, 93, 93]
                }
            }
        }
    },
    darkerBackground: {
        type: colorType,
        predicates: {
            ["dark"]: {
                any: {
                    default: [8, 8, 8, .5],
                }
            },
            ["light"]: {
                any: {
                    default: [8, 8, 8, .1]
                }
            }
        }
    },
    waveOpacity: {
        type: numberType,
        predicates: {
            any: {
                any: {
                    first: .2638160683363006,
                    second: .17587737889086702,
                    third: .17587737889086702,
                }
            }
        }
    },
    shadowOpacity: {
        type: numberType,
        predicates: {
            ["dark"]: {
                any: {
                    default: .3
                }
            },
            ["light"]: {
                any: {
                    default: .15
                }
            }
        }
    },
    waveColor: {
        type: colorType,
        predicates: {
            ["dark"]: {
                any: {
                    default: [32, 32, 32]
                }
            },
            ["light"]: {
                any: {
                    default: [228, 228, 228]
                }
            }
        }
    },
    stepIconBrightness: {
        type: numberType,
        predicates: {
            ["dark"]: {
                any: {
                    default: 5
                }
            },
            ["light"]: {
                any: {
                    default: 0
                }
            }
        }
    }
}

function buildSettingName(str) {
    s = ""
    for (let c of str) {
        if (/[A-Z]/.test(c)) {
            s += "-" + c.toLowerCase()
        } else {
            s += c
        }
    }
    return s
}

function applyTheme() {
    let progress = themeTransitionEnabled ? Math.min(1, (Date.now() - lastChange) / themeTransitionDuration) : 1

    vars.forEach(value => {
        let r = value.type(typeof value.from != "undefined" && value.from != null ? value.from : value.to, value.to, progress)
        currentValues[value.name] = r.value
        styleElement.style.setProperty("--" + value.name, (typeof r.cssValue != "undefined" && r.cssValue != null ? r.cssValue : r.value))

        if (typeof r.variants != "undefined" && r.variants != null) {
            for (let v in r.variants) {
                styleElement.style.setProperty("--" + value.name + "-" + buildSettingName(v), r.variants[v])
            }
        }
    })

    if (progress < 1) {
        animationHandle = requestAnimationFrame(applyTheme);
    } else {
        animationHandle = null;
    }
}

function switchTheme(theme, accentTheme) {
    if (theme == null) theme = localStorage.getItem("theme")
    else localStorage.setItem("theme", theme);


    if (accentTheme == null) accentTheme = localStorage.getItem("accent-theme")
    else localStorage.setItem("accent-theme", accentTheme);

    for (let key in settings) {
        let data = settings[key]
        for (let mt in data["predicates"]) {
            let mtData = data["predicates"][mt];
            if (mt === "any" || mt === theme || mt.includes(theme)) {
                for (let at in mtData) {
                    let atData = mtData[at]
                    if (at === "any" || at === accentTheme || at.includes(accentTheme)) {
                        for (let variant in atData) {
                            let value = atData[variant]
                            let name = buildSettingName(key)
                            if (variant !== "default") {
                                name += "-" + buildSettingName(variant)
                            }

                            vars.push({
                                name: name,
                                type: data.type,
                                from: currentValues[name],
                                to: value
                            })
                        }
                    }
                }
            }
        }
    }

    if (typeof animationHandle != 'undefined' && animationHandle != null) cancelAnimationFrame(animationHandle);
    animationHandle = requestAnimationFrame(applyTheme)
    lastChange = Date.now();
}

document.addEventListener("DOMContentLoaded", () => {
    styleElement = document.getElementsByTagName("html").item(0)

    let theme = localStorage.getItem("theme");
    let accentTheme = localStorage.getItem("accent-theme");

    if (theme == null || typeof theme == 'undefined') theme = defaultMainTheme
    if (accentTheme == null || typeof accentTheme == 'undefined') accentTheme = defaultAccentTheme;

    switchTheme(theme, accentTheme);

    let mainColorButtons = document.getElementsByClassName("main-theme-color");
    let accentColorButtons = document.getElementsByClassName("accent-theme-color");

    for (let b of mainColorButtons) {
        b.addEventListener("click", () => {
            switchTheme(b.getAttribute("data-color"), null)
        })
    }

    for (let b of accentColorButtons) {
        b.addEventListener("click", () => {
            switchTheme(null, b.getAttribute("data-color"))
        })
    }
})

window.addEventListener("load", () => {
    themeTransitionEnabled = true;
})

