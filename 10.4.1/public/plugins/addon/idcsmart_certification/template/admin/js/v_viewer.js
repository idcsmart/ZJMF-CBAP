(function webpackUniversalModuleDefinition(root, factory) {
    if (typeof exports === 'object' && typeof module === 'object')
        module.exports = factory(require("viewerjs"), require("vue"));
    else if (typeof define === 'function' && define.amd)
        define(["viewerjs", "vue"], factory);
    else if (typeof exports === 'object')
        exports["VueViewer"] = factory(require("viewerjs"), require("vue"));
    else
        root["VueViewer"] = factory(root["Viewer"], root["Vue"]);
})(this, function (__WEBPACK_EXTERNAL_MODULE_0__, __WEBPACK_EXTERNAL_MODULE_2__) {
    return /******/ (function (modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if (installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
                /******/
}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
                /******/
};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
            /******/
}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function (value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function (exports, name, getter) {
/******/ 		if (!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
        /******/
});
                /******/
}
            /******/
};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function (module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
            /******/
};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function (object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 6);
        /******/
})
/************************************************************************/
/******/([
/* 0 */
/***/ (function (module, exports) {

            module.exports = __WEBPACK_EXTERNAL_MODULE_0__;

            /***/
}),
/* 1 */
/***/ (function (module, __webpack_exports__, __webpack_require__) {

            "use strict";
/* harmony export (immutable) */ __webpack_exports__["a"] = extend;

            function extend() {
                var extended = {};
                var deep = false;
                var i = 0;
                var length = arguments.length;

                if (Object.prototype.toString.call(arguments[0]) === '[object Boolean]') {
                    deep = arguments[0];
                    i++;
                }

                function merge(obj) {
                    for (var prop in obj) {
                        if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                            if (deep && Object.prototype.toString.call(obj[prop]) === '[object Object]') {
                                extended[prop] = extend(true, extended[prop], obj[prop]);
                            } else {
                                extended[prop] = obj[prop];
                            }
                        }
                    }
                }

                for (; i < length; i++) {
                    var obj = arguments[i];
                    merge(obj);
                }

                return extended;
            }

            /***/
}),
/* 2 */
/***/ (function (module, exports) {

            module.exports = __WEBPACK_EXTERNAL_MODULE_2__;

            /***/
}),
/* 3 */
/***/ (function (module, __webpack_exports__, __webpack_require__) {

            "use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_viewerjs__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_viewerjs___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_viewerjs__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vue__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_vue__);




            var api = function api() {
                var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
                    _ref$images = _ref.images,
                    images = _ref$images === undefined ? [] : _ref$images,
                    _ref$options = _ref.options,
                    options = _ref$options === undefined ? {} : _ref$options;

                options = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1__utils__["a" /* extend */])(options, {
                    inline: false
                });

                var ViewerToken = __WEBPACK_IMPORTED_MODULE_2_vue___default.a.extend({
                    render: function render(h) {
                        return h('div', {
                            style: {
                                display: 'none'
                            },
                            class: ['__viewer-token']
                        }, images.map(function (attr) {
                            return h('img', {
                                attrs: typeof attr === 'string' ? { src: attr } : attr
                            });
                        }));
                    }
                });
                var token = new ViewerToken();
                token.$mount();
                document.body.appendChild(token.$el);

                var $viewer = new __WEBPACK_IMPORTED_MODULE_0_viewerjs___default.a(token.$el, options);
                var $destroy = $viewer.destroy.bind($viewer);
                $viewer.destroy = function () {
                    $destroy();
                    token.$destroy();
                    document.body.removeChild(token.$el);
                    return $viewer;
                };
                $viewer.show();

                token.$el.addEventListener('hidden', function () {
                    if (this.viewer === $viewer) {
                        $viewer.destroy();
                    }
                });

                return $viewer;
            };

/* harmony default export */ __webpack_exports__["a"] = (api);

            /***/
}),
/* 4 */
/***/ (function (module, __webpack_exports__, __webpack_require__) {

            "use strict";
/* WEBPACK VAR INJECTION */(function (global) {/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_viewerjs__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_viewerjs___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_viewerjs__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_throttle_debounce__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_throttle_debounce___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_throttle_debounce__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vue__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_vue__);




                var directive = function directive() {
                    var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
                        _ref$name = _ref.name,
                        name = _ref$name === undefined ? 'viewer' : _ref$name,
                        _ref$debug = _ref.debug,
                        debug = _ref$debug === undefined ? false : _ref$debug;

                    function createViewer(el, options) {
                        var rebuild = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
                        var observer = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

                        __WEBPACK_IMPORTED_MODULE_2_vue___default.a.nextTick(function () {
                            if (observer && !imageDiff(el)) return;
                            if (rebuild || !el['$' + name]) {
                                destroyViewer(el);
                                el['$' + name] = new __WEBPACK_IMPORTED_MODULE_0_viewerjs___default.a(el, options);
                                log('Viewer created');
                            } else {
                                el['$' + name].update();
                                log('Viewer updated');
                            }
                        });
                    }

                    function imageDiff(el) {
                        var imageContent = el.innerHTML.match(/<img([\w\W]+?)[\\/]?>/g);
                        var viewerImageText = imageContent ? imageContent.join('') : undefined;
                        if (el.__viewerImageDiffCache === viewerImageText) {
                            log('Element change detected, but image(s) has not changed');
                            return false;
                        } else {
                            log('Image change detected');
                            el.__viewerImageDiffCache = viewerImageText;
                            return true;
                        }
                    }

                    function createObserver(el, options, debouncedCreateViewer, rebuild) {
                        destroyObserver(el);
                        var MutationObserver = global.MutationObserver || global.WebKitMutationObserver || global.MozMutationObserver;
                        if (!MutationObserver) {
                            log('Observer not supported');
                            return;
                        }
                        var observer = new MutationObserver(function (mutations) {
                            mutations.forEach(function (mutation) {
                                log('Viewer mutation:' + mutation.type);
                                debouncedCreateViewer(el, options, rebuild, true);
                            });
                        });
                        var config = { attributes: true, childList: true, characterData: true, subtree: true };
                        observer.observe(el, config);
                        el.__viewerMutationObserver = observer;
                        log('Observer created');
                    }

                    function createWatcher(el, _ref2, vnode, debouncedCreateViewer) {
                        var expression = _ref2.expression;

                        var simplePathRE = /^[A-Za-z_$][\w$]*(?:\.[A-Za-z_$][\w$]*|\['[^']*?']|\["[^"]*?"]|\[\d+]|\[[A-Za-z_$][\w$]*])*$/;
                        if (!expression || !simplePathRE.test(expression)) {
                            log('Only simple dot-delimited paths can create watcher');
                            return;
                        }
                        el.__viewerUnwatch = vnode.context.$watch(expression, function (newVal, oldVal) {
                            log('Change detected by watcher: ', expression);
                            debouncedCreateViewer(el, newVal, true);
                        }, {
                            deep: true
                        });
                        log('Watcher created, expression: ', expression);
                    }

                    function destroyViewer(el) {
                        if (!el['$' + name]) {
                            return;
                        }
                        el['$' + name].destroy();
                        delete el['$' + name];
                        log('Viewer destroyed');
                    }

                    function destroyObserver(el) {
                        if (!el.__viewerMutationObserver) {
                            return;
                        }
                        el.__viewerMutationObserver.disconnect();
                        delete el.__viewerMutationObserver;
                        log('Observer destroyed');
                    }

                    function destroyWatcher(el) {
                        if (!el.__viewerUnwatch) {
                            return;
                        }
                        el.__viewerUnwatch();
                        delete el.__viewerUnwatch;
                        log('Watcher destroyed');
                    }

                    function log() {
                        var _console;

                        debug && (_console = console).log.apply(_console, arguments);
                    }

                    var directive = {
                        bind: function bind(el, binding, vnode) {
                            log('Viewer bind');
                            var debouncedCreateViewer = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1_throttle_debounce__["debounce"])(50, createViewer);
                            debouncedCreateViewer(el, binding.value);

                            createWatcher(el, binding, vnode, debouncedCreateViewer);

                            if (!binding.modifiers.static) {
                                createObserver(el, binding.value, debouncedCreateViewer, binding.modifiers.rebuild);
                            }
                        },
                        unbind: function unbind(el, binding) {
                            log('Viewer unbind');

                            destroyObserver(el);

                            destroyWatcher(el);

                            destroyViewer(el);
                        }
                    };

                    return directive;
                };

/* harmony default export */ __webpack_exports__["a"] = (directive);
                /* WEBPACK VAR INJECTION */
}.call(__webpack_exports__, __webpack_require__(9)))

            /***/
}),
/* 5 */
/***/ (function (module, exports, __webpack_require__) {

            var Component = __webpack_require__(10)(
                /* script */
                __webpack_require__(8),
                /* template */
                __webpack_require__(11),
                /* scopeId */
                null,
                /* cssModules */
                null
            )
            Component.options.__file = "/Volumes/public/Workspace/web/v-viewer/src/component.vue"
            if (Component.esModule && Object.keys(Component.esModule).some(function (key) { return key !== "default" && key !== "__esModule" })) { console.error("named exports are not supported in *.vue files.") }
            if (Component.options.functional) { console.error("[vue-loader] component.vue: functional components are not supported with templates, they should use render functions.") }

            /* hot reload */
            if (false) {
                (function () {
                    var hotAPI = require("vue-hot-reload-api")
                    hotAPI.install(require("vue"), false)
                    if (!hotAPI.compatible) return
                    module.hot.accept()
                    if (!module.hot.data) {
                        hotAPI.createRecord("data-v-3091014c", Component.options)
                    } else {
                        hotAPI.reload("data-v-3091014c", Component.options)
                    }
                })()
            }

            module.exports = Component.exports


            /***/
}),
/* 6 */
/***/ (function (module, __webpack_exports__, __webpack_require__) {

            "use strict";
            Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_viewerjs__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_viewerjs___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_viewerjs__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__component_vue__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__component_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__component_vue__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__directive__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__api__ = __webpack_require__(3);
/* harmony reexport (default from non-hamory) */ __webpack_require__.d(__webpack_exports__, "component", function () { return __WEBPACK_IMPORTED_MODULE_2__component_vue___default.a; });
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "directive", function () { return __WEBPACK_IMPORTED_MODULE_3__directive__["a"]; });
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "api", function () { return __WEBPACK_IMPORTED_MODULE_4__api__["a"]; });
/* harmony reexport (default from non-hamory) */ __webpack_require__.d(__webpack_exports__, "Viewer", function () { return __WEBPACK_IMPORTED_MODULE_1_viewerjs___default.a; });








/* harmony default export */ __webpack_exports__["default"] = ({
                install: function install(Vue) {
                    var _ref = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
                        _ref$name = _ref.name,
                        name = _ref$name === undefined ? 'viewer' : _ref$name,
                        _ref$debug = _ref.debug,
                        debug = _ref$debug === undefined ? false : _ref$debug,
                        defaultOptions = _ref.defaultOptions;

                    __WEBPACK_IMPORTED_MODULE_1_viewerjs___default.a.setDefaults(defaultOptions);

                    Vue.component(name, __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_0__utils__["a" /* extend */])(__WEBPACK_IMPORTED_MODULE_2__component_vue___default.a, { name: name }));
                    Vue.directive(name, __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3__directive__["a" /* default */])({ name: name, debug: debug }));
                    Vue.prototype['$' + name + 'Api'] = __WEBPACK_IMPORTED_MODULE_4__api__["a" /* default */];
                },
                setDefaults: function setDefaults(defaultOptions) {
                    __WEBPACK_IMPORTED_MODULE_1_viewerjs___default.a.setDefaults(defaultOptions);
                }
            });

            /***/
}),
/* 7 */
/***/ (function (module, exports, __webpack_require__) {

            var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__; var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

            (function (global, factory) {
                (false ? 'undefined' : _typeof(exports)) === 'object' && typeof module !== 'undefined' ? factory(exports) : true ? !(__WEBPACK_AMD_DEFINE_ARRAY__ = [exports], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
                    __WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
                        (__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
                    __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__)) : (global = global || self, factory(global.throttleDebounce = {}));
            })(this, function (exports) {
                'use strict';

                function throttle(delay, noTrailing, callback, debounceMode) {
                    var timeoutID;
                    var cancelled = false;

                    var lastExec = 0;

                    function clearExistingTimeout() {
                        if (timeoutID) {
                            clearTimeout(timeoutID);
                        }
                    }

                    function cancel() {
                        clearExistingTimeout();
                        cancelled = true;
                    }

                    if (typeof noTrailing !== 'boolean') {
                        debounceMode = callback;
                        callback = noTrailing;
                        noTrailing = undefined;
                    }


                    function wrapper() {
                        for (var _len = arguments.length, arguments_ = new Array(_len), _key = 0; _key < _len; _key++) {
                            arguments_[_key] = arguments[_key];
                        }

                        var self = this;
                        var elapsed = Date.now() - lastExec;

                        if (cancelled) {
                            return;
                        }

                        function exec() {
                            lastExec = Date.now();
                            callback.apply(self, arguments_);
                        }


                        function clear() {
                            timeoutID = undefined;
                        }

                        if (debounceMode && !timeoutID) {
                            exec();
                        }

                        clearExistingTimeout();

                        if (debounceMode === undefined && elapsed > delay) {
                            exec();
                        } else if (noTrailing !== true) {
                            timeoutID = setTimeout(debounceMode ? clear : exec, debounceMode === undefined ? delay - elapsed : delay);
                        }
                    }

                    wrapper.cancel = cancel;

                    return wrapper;
                }

                function debounce(delay, atBegin, callback) {
                    return callback === undefined ? throttle(delay, atBegin, false) : throttle(delay, callback, atBegin !== false);
                }

                exports.debounce = debounce;
                exports.throttle = throttle;

                Object.defineProperty(exports, '__esModule', { value: true });
            });

            /***/
}),
/* 8 */
/***/ (function (module, __webpack_exports__, __webpack_require__) {

            "use strict";
            Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_viewerjs__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_viewerjs___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_viewerjs__);




/* harmony default export */ __webpack_exports__["default"] = ({
                props: {
                    images: {
                        type: Array
                    },
                    rebuild: {
                        type: Boolean,
                        default: false
                    },
                    trigger: {},
                    options: {
                        type: Object
                    }
                },

                data: function data() {
                    return {};
                },


                computed: {},

                methods: {
                    onChange: function onChange() {
                        if (this.rebuild) {
                            this.rebuildViewer();
                        } else {
                            this.updateViewer();
                        }
                    },
                    rebuildViewer: function rebuildViewer() {
                        this.destroyViewer();
                        this.createViewer();
                    },
                    updateViewer: function updateViewer() {
                        if (this.$viewer) {
                            this.$viewer.update();
                            this.$emit('inited', this.$viewer);
                        } else {
                            this.createViewer();
                        }
                    },
                    destroyViewer: function destroyViewer() {
                        this.$viewer && this.$viewer.destroy();
                    },
                    createViewer: function createViewer() {
                        this.$viewer = new __WEBPACK_IMPORTED_MODULE_0_viewerjs___default.a(this.$el, this.options);
                        this.$emit('inited', this.$viewer);
                    }
                },

                watch: {
                    images: function images() {
                        var _this = this;

                        this.$nextTick(function () {
                            _this.onChange();
                        });
                    },

                    trigger: {
                        handler: function handler() {
                            var _this2 = this;

                            this.$nextTick(function () {
                                _this2.onChange();
                            });
                        },

                        deep: true
                    },
                    options: {
                        handler: function handler() {
                            var _this3 = this;

                            this.$nextTick(function () {
                                _this3.rebuildViewer();
                            });
                        },

                        deep: true
                    }
                },

                mounted: function mounted() {
                    this.createViewer();
                },
                destroyed: function destroyed() {
                    this.destroyViewer();
                }
            });

            /***/
}),
/* 9 */
/***/ (function (module, exports) {

            var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

            var g;

            g = function () {
                return this;
            }();

            try {
                g = g || Function("return this")() || (1, eval)("this");
            } catch (e) {
                if ((typeof window === "undefined" ? "undefined" : _typeof(window)) === "object") g = window;
            }

            module.exports = g;

            /***/
}),
/* 10 */
/***/ (function (module, exports) {

            // this module is a runtime utility for cleaner component module output and will
            // be included in the final webpack user bundle

            module.exports = function normalizeComponent(
                rawScriptExports,
                compiledTemplate,
                scopeId,
                cssModules
            ) {
                var esModule
                var scriptExports = rawScriptExports = rawScriptExports || {}

                // ES6 modules interop
                var type = typeof rawScriptExports.default
                if (type === 'object' || type === 'function') {
                    esModule = rawScriptExports
                    scriptExports = rawScriptExports.default
                }

                // Vue.extend constructor export interop
                var options = typeof scriptExports === 'function'
                    ? scriptExports.options
                    : scriptExports

                // render functions
                if (compiledTemplate) {
                    options.render = compiledTemplate.render
                    options.staticRenderFns = compiledTemplate.staticRenderFns
                }

                // scopedId
                if (scopeId) {
                    options._scopeId = scopeId
                }

                // inject cssModules
                if (cssModules) {
                    var computed = Object.create(options.computed || null)
                    Object.keys(cssModules).forEach(function (key) {
                        var module = cssModules[key]
                        computed[key] = function () { return module }
                    })
                    options.computed = computed
                }

                return {
                    esModule: esModule,
                    exports: scriptExports,
                    options: options
                }
            }


            /***/
}),
/* 11 */
/***/ (function (module, exports, __webpack_require__) {

            module.exports = {
                render: function () {
                    var _vm = this; var _h = _vm.$createElement; var _c = _vm._self._c || _h;
                    return _c('div', [_vm._t("default", null, {
                        "images": _vm.images,
                        "options": _vm.options
                    })], 2)
                }, staticRenderFns: []
            }
            module.exports.render._withStripped = true
            if (false) {
                module.hot.accept()
                if (module.hot.data) {
                    require("vue-hot-reload-api").rerender("data-v-3091014c", module.exports)
                }
            }

            /***/
})
/******/]);
});