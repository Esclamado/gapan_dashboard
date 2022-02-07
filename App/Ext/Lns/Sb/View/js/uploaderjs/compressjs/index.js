! function(t, e) {
    "object" == typeof exports && "object" == typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define([], e) : "object" == typeof exports ? exports.Compress = e() : t.Compress = e()
}(this, function() {
    return function(t) {
        function e(i) {
            if (n[i]) return n[i].exports;
            var a = n[i] = {
                exports: {},
                id: i,
                loaded: !1
            };
            return t[i].call(a.exports, a, a.exports, e), a.loaded = !0, a.exports
        }
        var n = {};
        return e.m = t, e.c = n, e.p = "", e(0)
    }([function(t, e, n) {
        var i, a, r;
        ! function(o, u) {
            a = [t, n(1), n(2), n(3), n(4), n(5), n(6)], i = u, r = "function" == typeof i ? i.apply(e, a) : i, !(void 0 !== r && (t.exports = r))
        }(this, function(t, e, n, i, a, r, o) {
            "use strict";

            function u(t) {
                return t && t.__esModule ? t : {
                    default: t
                }
            }

            function s(t) {
                if (Array.isArray(t)) {
                    for (var e = 0, n = Array(t.length); e < t.length; e++) n[e] = t[e];
                    return n
                }
                return Array.from(t)
            }

            function f(t, e) {
                if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
            }
            var d = u(e),
                l = u(n),
                c = u(i),
                h = u(a),
                v = u(r),
                p = u(o),
                g = function() {
                    function t(t, e) {
                        for (var n = 0; n < e.length; n++) {
                            var i = e[n];
                            i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(t, i.key, i)
                        }
                    }
                    return function(e, n, i) {
                        return n && t(e.prototype, n), i && t(e, i), e
                    }
                }(),
                w = function() {
                    function t() {
                        f(this, t)
                    }
                    return g(t, [{
                        key: "attach",
                        value: function(t, e) {
                            var n = this;
                            return new Promise(function(i, a) {
                                var r = document.querySelector(t);
                                r.setAttribute("accept", "image/*"), r.addEventListener("change", function(t) {
                                    var a = n.compress([].concat(s(t.target.files)), e);
                                    i(a)
                                }, !1)
                            })
                        }
                    }, {
                        key: "compress",
                        value: function(t, e) {
                            function n(t, e) {
                                var n = new v.default(e);
                                return n.start = window.performance.now(), n.alt = t.name, n.ext = t.type, n.startSize = t.size, p.default.orientation(t).then(function(e) {
                                    return n.orientation = e, c.default.load(t)
                                }).then(i(n))
                            }

                            function i(t) {
                                return function(e) {
                                    return h.default.load(e).then(function(e) {
                                        if (t.startWidth = e.naturalWidth, t.startHeight = e.naturalHeight, t.resize) {
                                            var n = h.default.resize(t.maxWidth, t.maxHeight)(e.naturalWidth, e.naturalHeight),
                                                i = n.width,
                                                a = n.height;
                                            t.endWidth = i, t.endHeight = a
                                        } else t.endWidth = e.naturalWidth, t.endHeight = e.naturalHeight;
                                        return l.default.imageToCanvas(t.endWidth, t.endHeight, t.orientation)(e)
                                    }).then(function(e) {
                                        return t.iterations = 1, t.base64prefix = d.default.prefix(t.ext), a(e, t.startSize, t.quality, t.size, t.minQuality, t.iterations)
                                    }).then(function(e) {
                                        return t.finalSize = d.default.size(e), d.default.data(e)
                                    }).then(function(e) {
                                        t.end = window.performance.now();
                                        var n = t.end - t.start;
                                        return {
                                            data: e,
                                            prefix: t.base64prefix,
                                            elapsedTimeInSeconds: n / 1e3,
                                            alt: t.alt,
                                            initialSizeInMb: l.default.size(t.startSize).MB,
                                            endSizeInMb: l.default.size(t.finalSize).MB,
                                            ext: t.ext,
                                            quality: t.quality,
                                            endWidthInPx: t.endWidth,
                                            endHeightInPx: t.endHeight,
                                            initialWidthInPx: t.startWidth,
                                            initialHeightInPx: t.startHeight,
                                            sizeReducedInPercent: (t.startSize - t.finalSize) / t.startSize * 100,
                                            iterations: t.iterations
                                        }
                                    })
                                }
                            }

                            function a(t, e) {
                                var n = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : 1,
                                    i = arguments[3],
                                    r = arguments.length > 4 && void 0 !== arguments[4] ? arguments[4] : 1,
                                    o = arguments[5],
                                    u = l.default.canvasToBase64(t, n),
                                    s = d.default.size(u);
                                return o += 1, s > i ? a(t, s, n - .1, i, r, o) : n > r ? a(t, s, n - .1, i, r, o) : n < .5 ? u : u
                            }
                            return Promise.all(t.map(function(t) {
                                return n(t, e)
                            }))
                        }
                    }], [{
                        key: "convertBase64ToFile",
                        value: function(t, e) {
                            return l.default.base64ToFile(t, e)
                        }
                    }]), t
                }();
            t.exports = w
        })
    }, function(t, e, n) {
        var i, a, r;
        ! function(n, o) {
            a = [e], i = o, r = "function" == typeof i ? i.apply(e, a) : i, !(void 0 !== r && (t.exports = r))
        }(this, function(t) {
            "use strict";
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var e = function(t) {
                    var e = t.replace(/^data:image\/\w+;base64,/, "").length;
                    return (e - 814) / 1.37
                },
                n = function(t) {
                    return t.split(";")[0].match(/jpeg|png|gif/)[0]
                },
                i = function(t) {
                    return t.replace(/^data:image\/\w+;base64,/, "")
                },
                a = function(t) {
                    return "data:" + t + ";base64,"
                };
            t.default = {
                size: e,
                mime: n,
                data: i,
                prefix: a
            }
        })
    }, function(t, e, n) {
        var i, a, r;
        ! function(n, o) {
            a = [e], i = o, r = "function" == typeof i ? i.apply(e, a) : i, !(void 0 !== r && (t.exports = r))
        }(this, function(t) {
            "use strict";
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var e = function(t) {
                    for (var e = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "image/jpeg", n = window.atob(t), i = [], a = 0; a < n.length; a++) i[a] = n.charCodeAt(a);
                    return new window.Blob([new Uint8Array(i)], {
                        type: e
                    })
                },
                n = function(t, e, n) {
                    var i = document.createElement("canvas"),
                        a = i.getContext("2d");
                    return i.width = t, i.height = e,
                        function(r) {
                            if (!n || n > 8) return a.drawImage(r, 0, 0, i.width, i.height), i;
                            switch (n > 4 && (i.width = e, i.height = t), n) {
                                case 2:
                                    a.translate(t, 0), a.scale(-1, 1);
                                    break;
                                case 3:
                                    a.translate(t, e), a.rotate(Math.PI);
                                    break;
                                case 4:
                                    a.translate(0, e), a.scale(1, -1);
                                    break;
                                case 5:
                                    a.rotate(.5 * Math.PI), a.scale(1, -1);
                                    break;
                                case 6:
                                    a.rotate(.5 * Math.PI), a.translate(0, -e);
                                    break;
                                case 7:
                                    a.rotate(.5 * Math.PI), a.translate(t, -e), a.scale(-1, 1);
                                    break;
                                case 8:
                                    a.rotate(-.5 * Math.PI), a.translate(-t, 0)
                            }
                            return n > 4 ? a.drawImage(r, 0, 0, i.height, i.width) : a.drawImage(r, 0, 0, i.width, i.height), i
                        }
                },
                i = function(t) {
                    var e = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : .75,
                        n = t.toDataURL("image/jpeg", e);
                    return n
                },
                a = function(t) {
                    return {
                        KB: t / 1e3,
                        MB: t / 1e6
                    }
                };
            t.default = {
                base64ToFile: e,
                imageToCanvas: n,
                canvasToBase64: i,
                size: a
            }
        })
    }, function(t, e, n) {
        var i, a, r;
        ! function(n, o) {
            a = [e], i = o, r = "function" == typeof i ? i.apply(e, a) : i, !(void 0 !== r && (t.exports = r))
        }(this, function(t) {
            "use strict";
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var e = function(t) {
                return new Promise(function(e, n) {
                    var i = new window.FileReader;
                    i.addEventListener("load", function(t) {
                        e(t.target.result)
                    }, !1), i.addEventListener("error", function(t) {
                        n(t)
                    }, !1), i.readAsDataURL(t)
                })
            };
            t.default = {
                load: e
            }
        })
    }, function(t, e, n) {
        var i, a, r;
        ! function(n, o) {
            a = [e], i = o, r = "function" == typeof i ? i.apply(e, a) : i, !(void 0 !== r && (t.exports = r))
        }(this, function(t) {
            "use strict";
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var e = function(t) {
                    return new Promise(function(e, n) {
                        var i = new window.Image;
                        i.addEventListener("load", function() {
                            e(i)
                        }, !1), i.addEventListener("error", function(t) {
                            n(t)
                        }, !1), i.src = t
                    })
                },
                n = function(t, e) {
                    return function(n, i) {
                        if (!t && !e) return {
                            width: n,
                            height: i
                        };
                        var a = n / i,
                            r = t / e,
                            o = void 0,
                            u = void 0;
                        return a > r ? (o = Math.min(n, t), u = o / a) : (u = Math.min(i, e), o = u * a), {
                            width: o,
                            height: u
                        }
                    }
                };
            t.default = {
                load: e,
                resize: n
            }
        })
    }, function(t, e, n) {
        var i, a, r;
        ! function(n, o) {
            a = [e], i = o, r = "function" == typeof i ? i.apply(e, a) : i, !(void 0 !== r && (t.exports = r))
        }(this, function(t) {
            "use strict";

            function e(t, e) {
                if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var n = function t(n) {
                var i = n.quality,
                    a = void 0 === i ? .75 : i,
                    r = n.size,
                    o = void 0 === r ? 2 : r,
                    u = n.maxWidth,
                    s = void 0 === u ? 1920 : u,
                    f = n.maxHeight,
                    d = void 0 === f ? 1920 : f,
                    l = n.resize,
                    c = void 0 === l || l;
                e(this, t), this.start = window.performance.now(), this.end = null, this.alt = null, this.ext = null, this.startSize = null, this.startWidth = null, this.startHeight = null, this.size = 1e3 * o * 1e3, this.endSize = null, this.endWidth = null, this.endHeight = null, this.iterations = 0, this.base64prefix = null, this.quality = a, this.resize = c, this.maxWidth = s, this.maxHeight = d, this.orientation = 1
            };
            t.default = n
        })
    }, function(t, e, n) {
        var i, a, r;
        ! function(n, o) {
            a = [e], i = o, r = "function" == typeof i ? i.apply(e, a) : i, !(void 0 !== r && (t.exports = r))
        }(this, function(t) {
            "use strict";
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var e = function(t) {
                return new Promise(function(e, n) {
                    var i = new window.FileReader;
                    i.onload = function(t) {
                        var n = new DataView(t.target.result);
                        65496 !== n.getUint16(0, !1) && e(-2);
                        for (var i = n.byteLength, a = 2; a < i;) {
                            var r = n.getUint16(a, !1);
                            if (a += 2, 65505 === r) {
                                1165519206 !== n.getUint32(a += 2, !1) && e(-1);
                                var o = 18761 === n.getUint16(a += 6, !1);
                                a += n.getUint32(a + 4, o);
                                var u = n.getUint16(a, o);
                                a += 2;
                                for (var s = 0; s < u; s++) 274 === n.getUint16(a + 12 * s, o) && e(n.getUint16(a + 12 * s + 8, o))
                            } else {
                                if (65280 !== (65280 & r)) break;
                                a += n.getUint16(a, !1)
                            }
                        }
                        e(-1)
                    }, i.readAsArrayBuffer(t.slice(0, 65536))
                })
            };
            t.default = {
                orientation: e
            }
        })
    }])
});
//# sourceMappingURL=index.js.map