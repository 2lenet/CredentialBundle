/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/toastify-js/src/toastify.js"
/*!**************************************************!*\
  !*** ./node_modules/toastify-js/src/toastify.js ***!
  \**************************************************/
(module) {

/*!
 * Toastify js 1.12.0
 * https://github.com/apvarun/toastify-js
 * @license MIT licensed
 *
 * Copyright (C) 2018 Varun A P
 */
(function(root, factory) {
  if ( true && module.exports) {
    module.exports = factory();
  } else {
    root.Toastify = factory();
  }
})(this, function(global) {
  // Object initialization
  var Toastify = function(options) {
      // Returning a new init object
      return new Toastify.lib.init(options);
    },
    // Library version
    version = "1.12.0";

  // Set the default global options
  Toastify.defaults = {
    oldestFirst: true,
    text: "Toastify is awesome!",
    node: undefined,
    duration: 3000,
    selector: undefined,
    callback: function () {
    },
    destination: undefined,
    newWindow: false,
    close: false,
    gravity: "toastify-top",
    positionLeft: false,
    position: '',
    backgroundColor: '',
    avatar: "",
    className: "",
    stopOnFocus: true,
    onClick: function () {
    },
    offset: {x: 0, y: 0},
    escapeMarkup: true,
    ariaLive: 'polite',
    style: {background: ''}
  };

  // Defining the prototype of the object
  Toastify.lib = Toastify.prototype = {
    toastify: version,

    constructor: Toastify,

    // Initializing the object with required parameters
    init: function(options) {
      // Verifying and validating the input object
      if (!options) {
        options = {};
      }

      // Creating the options object
      this.options = {};

      this.toastElement = null;

      // Validating the options
      this.options.text = options.text || Toastify.defaults.text; // Display message
      this.options.node = options.node || Toastify.defaults.node;  // Display content as node
      this.options.duration = options.duration === 0 ? 0 : options.duration || Toastify.defaults.duration; // Display duration
      this.options.selector = options.selector || Toastify.defaults.selector; // Parent selector
      this.options.callback = options.callback || Toastify.defaults.callback; // Callback after display
      this.options.destination = options.destination || Toastify.defaults.destination; // On-click destination
      this.options.newWindow = options.newWindow || Toastify.defaults.newWindow; // Open destination in new window
      this.options.close = options.close || Toastify.defaults.close; // Show toast close icon
      this.options.gravity = options.gravity === "bottom" ? "toastify-bottom" : Toastify.defaults.gravity; // toast position - top or bottom
      this.options.positionLeft = options.positionLeft || Toastify.defaults.positionLeft; // toast position - left or right
      this.options.position = options.position || Toastify.defaults.position; // toast position - left or right
      this.options.backgroundColor = options.backgroundColor || Toastify.defaults.backgroundColor; // toast background color
      this.options.avatar = options.avatar || Toastify.defaults.avatar; // img element src - url or a path
      this.options.className = options.className || Toastify.defaults.className; // additional class names for the toast
      this.options.stopOnFocus = options.stopOnFocus === undefined ? Toastify.defaults.stopOnFocus : options.stopOnFocus; // stop timeout on focus
      this.options.onClick = options.onClick || Toastify.defaults.onClick; // Callback after click
      this.options.offset = options.offset || Toastify.defaults.offset; // toast offset
      this.options.escapeMarkup = options.escapeMarkup !== undefined ? options.escapeMarkup : Toastify.defaults.escapeMarkup;
      this.options.ariaLive = options.ariaLive || Toastify.defaults.ariaLive;
      this.options.style = options.style || Toastify.defaults.style;
      if(options.backgroundColor) {
        this.options.style.background = options.backgroundColor;
      }

      // Returning the current object for chaining functions
      return this;
    },

    // Building the DOM element
    buildToast: function() {
      // Validating if the options are defined
      if (!this.options) {
        throw "Toastify is not initialized";
      }

      // Creating the DOM object
      var divElement = document.createElement("div");
      divElement.className = "toastify on " + this.options.className;

      // Positioning toast to left or right or center
      if (!!this.options.position) {
        divElement.className += " toastify-" + this.options.position;
      } else {
        // To be depreciated in further versions
        if (this.options.positionLeft === true) {
          divElement.className += " toastify-left";
          console.warn('Property `positionLeft` will be depreciated in further versions. Please use `position` instead.')
        } else {
          // Default position
          divElement.className += " toastify-right";
        }
      }

      // Assigning gravity of element
      divElement.className += " " + this.options.gravity;

      if (this.options.backgroundColor) {
        // This is being deprecated in favor of using the style HTML DOM property
        console.warn('DEPRECATION NOTICE: "backgroundColor" is being deprecated. Please use the "style.background" property.');
      }

      // Loop through our style object and apply styles to divElement
      for (var property in this.options.style) {
        divElement.style[property] = this.options.style[property];
      }

      // Announce the toast to screen readers
      if (this.options.ariaLive) {
        divElement.setAttribute('aria-live', this.options.ariaLive)
      }

      // Adding the toast message/node
      if (this.options.node && this.options.node.nodeType === Node.ELEMENT_NODE) {
        // If we have a valid node, we insert it
        divElement.appendChild(this.options.node)
      } else {
        if (this.options.escapeMarkup) {
          divElement.innerText = this.options.text;
        } else {
          divElement.innerHTML = this.options.text;
        }

        if (this.options.avatar !== "") {
          var avatarElement = document.createElement("img");
          avatarElement.src = this.options.avatar;

          avatarElement.className = "toastify-avatar";

          if (this.options.position == "left" || this.options.positionLeft === true) {
            // Adding close icon on the left of content
            divElement.appendChild(avatarElement);
          } else {
            // Adding close icon on the right of content
            divElement.insertAdjacentElement("afterbegin", avatarElement);
          }
        }
      }

      // Adding a close icon to the toast
      if (this.options.close === true) {
        // Create a span for close element
        var closeElement = document.createElement("button");
        closeElement.type = "button";
        closeElement.setAttribute("aria-label", "Close");
        closeElement.className = "toast-close";
        closeElement.innerHTML = "&#10006;";

        // Triggering the removal of toast from DOM on close click
        closeElement.addEventListener(
          "click",
          function(event) {
            event.stopPropagation();
            this.removeElement(this.toastElement);
            window.clearTimeout(this.toastElement.timeOutValue);
          }.bind(this)
        );

        //Calculating screen width
        var width = window.innerWidth > 0 ? window.innerWidth : screen.width;

        // Adding the close icon to the toast element
        // Display on the right if screen width is less than or equal to 360px
        if ((this.options.position == "left" || this.options.positionLeft === true) && width > 360) {
          // Adding close icon on the left of content
          divElement.insertAdjacentElement("afterbegin", closeElement);
        } else {
          // Adding close icon on the right of content
          divElement.appendChild(closeElement);
        }
      }

      // Clear timeout while toast is focused
      if (this.options.stopOnFocus && this.options.duration > 0) {
        var self = this;
        // stop countdown
        divElement.addEventListener(
          "mouseover",
          function(event) {
            window.clearTimeout(divElement.timeOutValue);
          }
        )
        // add back the timeout
        divElement.addEventListener(
          "mouseleave",
          function() {
            divElement.timeOutValue = window.setTimeout(
              function() {
                // Remove the toast from DOM
                self.removeElement(divElement);
              },
              self.options.duration
            )
          }
        )
      }

      // Adding an on-click destination path
      if (typeof this.options.destination !== "undefined") {
        divElement.addEventListener(
          "click",
          function(event) {
            event.stopPropagation();
            if (this.options.newWindow === true) {
              window.open(this.options.destination, "_blank");
            } else {
              window.location = this.options.destination;
            }
          }.bind(this)
        );
      }

      if (typeof this.options.onClick === "function" && typeof this.options.destination === "undefined") {
        divElement.addEventListener(
          "click",
          function(event) {
            event.stopPropagation();
            this.options.onClick();
          }.bind(this)
        );
      }

      // Adding offset
      if(typeof this.options.offset === "object") {

        var x = getAxisOffsetAValue("x", this.options);
        var y = getAxisOffsetAValue("y", this.options);

        var xOffset = this.options.position == "left" ? x : "-" + x;
        var yOffset = this.options.gravity == "toastify-top" ? y : "-" + y;

        divElement.style.transform = "translate(" + xOffset + "," + yOffset + ")";

      }

      // Returning the generated element
      return divElement;
    },

    // Displaying the toast
    showToast: function() {
      // Creating the DOM object for the toast
      this.toastElement = this.buildToast();

      // Getting the root element to with the toast needs to be added
      var rootElement;
      if (typeof this.options.selector === "string") {
        rootElement = document.getElementById(this.options.selector);
      } else if (this.options.selector instanceof HTMLElement || (typeof ShadowRoot !== 'undefined' && this.options.selector instanceof ShadowRoot)) {
        rootElement = this.options.selector;
      } else {
        rootElement = document.body;
      }

      // Validating if root element is present in DOM
      if (!rootElement) {
        throw "Root element is not defined";
      }

      // Adding the DOM element
      var elementToInsert = Toastify.defaults.oldestFirst ? rootElement.firstChild : rootElement.lastChild;
      rootElement.insertBefore(this.toastElement, elementToInsert);

      // Repositioning the toasts in case multiple toasts are present
      Toastify.reposition();

      if (this.options.duration > 0) {
        this.toastElement.timeOutValue = window.setTimeout(
          function() {
            // Remove the toast from DOM
            this.removeElement(this.toastElement);
          }.bind(this),
          this.options.duration
        ); // Binding `this` for function invocation
      }

      // Supporting function chaining
      return this;
    },

    hideToast: function() {
      if (this.toastElement.timeOutValue) {
        clearTimeout(this.toastElement.timeOutValue);
      }
      this.removeElement(this.toastElement);
    },

    // Removing the element from the DOM
    removeElement: function(toastElement) {
      // Hiding the element
      // toastElement.classList.remove("on");
      toastElement.className = toastElement.className.replace(" on", "");

      // Removing the element from DOM after transition end
      window.setTimeout(
        function() {
          // remove options node if any
          if (this.options.node && this.options.node.parentNode) {
            this.options.node.parentNode.removeChild(this.options.node);
          }

          // Remove the element from the DOM, only when the parent node was not removed before.
          if (toastElement.parentNode) {
            toastElement.parentNode.removeChild(toastElement);
          }

          // Calling the callback function
          this.options.callback.call(toastElement);

          // Repositioning the toasts again
          Toastify.reposition();
        }.bind(this),
        400
      ); // Binding `this` for function invocation
    },
  };

  // Positioning the toasts on the DOM
  Toastify.reposition = function() {

    // Top margins with gravity
    var topLeftOffsetSize = {
      top: 15,
      bottom: 15,
    };
    var topRightOffsetSize = {
      top: 15,
      bottom: 15,
    };
    var offsetSize = {
      top: 15,
      bottom: 15,
    };

    // Get all toast messages on the DOM
    var allToasts = document.getElementsByClassName("toastify");

    var classUsed;

    // Modifying the position of each toast element
    for (var i = 0; i < allToasts.length; i++) {
      // Getting the applied gravity
      if (containsClass(allToasts[i], "toastify-top") === true) {
        classUsed = "toastify-top";
      } else {
        classUsed = "toastify-bottom";
      }

      var height = allToasts[i].offsetHeight;
      classUsed = classUsed.substr(9, classUsed.length-1)
      // Spacing between toasts
      var offset = 15;

      var width = window.innerWidth > 0 ? window.innerWidth : screen.width;

      // Show toast in center if screen with less than or equal to 360px
      if (width <= 360) {
        // Setting the position
        allToasts[i].style[classUsed] = offsetSize[classUsed] + "px";

        offsetSize[classUsed] += height + offset;
      } else {
        if (containsClass(allToasts[i], "toastify-left") === true) {
          // Setting the position
          allToasts[i].style[classUsed] = topLeftOffsetSize[classUsed] + "px";

          topLeftOffsetSize[classUsed] += height + offset;
        } else {
          // Setting the position
          allToasts[i].style[classUsed] = topRightOffsetSize[classUsed] + "px";

          topRightOffsetSize[classUsed] += height + offset;
        }
      }
    }

    // Supporting function chaining
    return this;
  };

  // Helper function to get offset.
  function getAxisOffsetAValue(axis, options) {

    if(options.offset[axis]) {
      if(isNaN(options.offset[axis])) {
        return options.offset[axis];
      }
      else {
        return options.offset[axis] + 'px';
      }
    }

    return '0px';

  }

  function containsClass(elem, yourClass) {
    if (!elem || typeof yourClass !== "string") {
      return false;
    } else if (
      elem.className &&
      elem.className
        .trim()
        .split(/\s+/gi)
        .indexOf(yourClass) > -1
    ) {
      return true;
    } else {
      return false;
    }
  }

  // Setting up the prototype for the init object
  Toastify.lib.init.prototype = Toastify.lib;

  // Returning the Toastify function to be assigned to the window object/module
  return Toastify;
});


/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!*******************************!*\
  !*** ./assets/scripts/app.js ***!
  \*******************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var toastify_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! toastify-js */ "./node_modules/toastify-js/src/toastify.js");
/* harmony import */ var toastify_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(toastify_js__WEBPACK_IMPORTED_MODULE_0__);
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }

window.addEventListener('load', function () {
  var matrice = document.getElementById('lle-credential-matrice');
  if (matrice) {
    checkSections();
    checkGroups();
    checkAllCredentialsOfGroup();
    checkAllCredentialsOfSection();
    checkCredential();
    enableCredentialByStatus();
    checkCredentialByStatus();
  }
});
function checkSections() {
  var sectionCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-section');

  // Check sectionCheckbox if all credentials are checked
  var _iterator = _createForOfIteratorHelper(sectionCheckboxes),
    _step;
  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var sectionCheckbox = _step.value;
      var groupId = sectionCheckbox.dataset.groupId;
      var sectionName = sectionCheckbox.dataset.sectionName;
      var allChecked = true;
      var checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section-' + sectionName + '-credential');
      var _iterator2 = _createForOfIteratorHelper(checkboxes),
        _step2;
      try {
        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
          var checkbox = _step2.value;
          if (!checkbox.checked) {
            allChecked = false;
            break;
          }
        }
      } catch (err) {
        _iterator2.e(err);
      } finally {
        _iterator2.f();
      }
      sectionCheckbox.checked = allChecked;
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
}
function checkGroups() {
  var groupCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group');

  // Check groupCheckbox if all sections are checked
  var _iterator3 = _createForOfIteratorHelper(groupCheckboxes),
    _step3;
  try {
    for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
      var groupCheckbox = _step3.value;
      var groupId = groupCheckbox.dataset.groupId;
      var allChecked = true;
      var checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section');
      var _iterator4 = _createForOfIteratorHelper(checkboxes),
        _step4;
      try {
        for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
          var checkbox = _step4.value;
          if (!checkbox.checked) {
            allChecked = false;
            break;
          }
        }
      } catch (err) {
        _iterator4.e(err);
      } finally {
        _iterator4.f();
      }
      groupCheckbox.checked = allChecked;
    }
  } catch (err) {
    _iterator3.e(err);
  } finally {
    _iterator3.f();
  }
}
var msg = window.lleCredentialMessages || {};
var GENERIC_ERROR = msg.genericError || 'An error occurred. Please contact an administrator.';

/**
 * Fetch a URL and resolve with { ok, remoteError, error }.
 * - ok: true if HTTP 2xx
 * - remoteError: string if local succeeded but remote failed
 * - error: generic message if local failed (HTTP error or network error)
 * Never rejects.
 */
function apiCall(_x) {
  return _apiCall.apply(this, arguments);
}
function _apiCall() {
  _apiCall = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(url) {
    var response, data, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          _context.n = 1;
          return fetch(url, {
            method: 'post'
          });
        case 1:
          response = _context.v;
          _context.n = 2;
          return response.json()["catch"](function () {
            return null;
          });
        case 2:
          data = _context.v;
          return _context.a(2, {
            ok: response.ok,
            remoteError: data && data.remoteError ? data.remoteError : null,
            error: response.ok ? null : data && data.error ? data.error : GENERIC_ERROR
          });
        case 3:
          _context.p = 3;
          _t = _context.v;
          return _context.a(2, {
            ok: false,
            remoteError: null,
            error: GENERIC_ERROR
          });
      }
    }, _callee, null, [[0, 3]]);
  }));
  return _apiCall.apply(this, arguments);
}
function checkAllCredentialsOfGroup() {
  var groupCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group');
  groupCheckboxes.forEach(function (groupCheckbox) {
    groupCheckbox.addEventListener('click', function () {
      var shouldCheck = window.confirm(groupCheckbox.dataset.confirmMessage);
      if (!shouldCheck) {
        groupCheckbox.checked = !groupCheckbox.checked;
        return;
      }
      var groupId = groupCheckbox.dataset.groupId;
      apiCall('/admin/credential/toggle-group/' + groupId + '/' + (groupCheckbox.checked ? 1 : 0)).then(function (_ref) {
        var ok = _ref.ok,
          remoteError = _ref.remoteError,
          error = _ref.error;
        if (!ok) {
          groupCheckbox.checked = !groupCheckbox.checked;
          showToast(error, "danger");
          return;
        }
        var checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-credential');
        checkboxes.forEach(function (checkbox) {
          checkbox.checked = groupCheckbox.checked;
        });
        var sectionCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section');
        sectionCheckboxes.forEach(function (sectionCheckbox) {
          sectionCheckbox.checked = groupCheckbox.checked;
        });
        if (remoteError) {
          showToast(remoteError, "warning");
        } else {
          var _msg$toggleGroup;
          showToast(((_msg$toggleGroup = msg.toggleGroup) === null || _msg$toggleGroup === void 0 ? void 0 : _msg$toggleGroup.success) || 'Success', "success");
        }
      });
    });
  });
}
function checkAllCredentialsOfSection() {
  var sectionCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-section');
  sectionCheckboxes.forEach(function (sectionCheckbox) {
    sectionCheckbox.addEventListener('click', function () {
      var shouldCheck = window.confirm(sectionCheckbox.dataset.confirmMessage);
      if (!shouldCheck) {
        sectionCheckbox.checked = !sectionCheckbox.checked;
        return;
      }
      var groupId = sectionCheckbox.dataset.groupId;
      var sectionName = sectionCheckbox.dataset.sectionName;
      apiCall('/admin/credential/toggle-section/' + sectionName + '/' + groupId + '/' + (sectionCheckbox.checked ? 1 : 0)).then(function (_ref2) {
        var ok = _ref2.ok,
          remoteError = _ref2.remoteError,
          error = _ref2.error;
        if (!ok) {
          sectionCheckbox.checked = !sectionCheckbox.checked;
          showToast(error, "danger");
          return;
        }
        var checkboxes = document.querySelectorAll('.lle-credential-checkbox-group-' + groupId + '-section-' + sectionName + '-credential');
        checkboxes.forEach(function (checkbox) {
          checkbox.checked = sectionCheckbox.checked;
        });
        if (remoteError) {
          showToast(remoteError, "warning");
        } else {
          var _msg$toggleSection;
          showToast(((_msg$toggleSection = msg.toggleSection) === null || _msg$toggleSection === void 0 ? void 0 : _msg$toggleSection.success) || 'Success', "success");
        }
      });
      checkGroups();
    });
  });
}
function checkCredential() {
  var credentialCheckboxes = document.querySelectorAll('.lle-credential-checkbox');
  credentialCheckboxes.forEach(function (credentialCheckbox) {
    credentialCheckbox.addEventListener('click', function () {
      var groupId = credentialCheckbox.dataset.groupId;
      var credentialId = credentialCheckbox.dataset.credentialId;
      apiCall('/admin/credential/toggle-credential/' + credentialId + '/' + groupId + '/' + (credentialCheckbox.checked ? 1 : 0)).then(function (_ref3) {
        var ok = _ref3.ok,
          remoteError = _ref3.remoteError,
          error = _ref3.error;
        if (!ok) {
          credentialCheckbox.checked = !credentialCheckbox.checked;
          showToast(error, "danger");
          return;
        }
        if (remoteError) {
          showToast(remoteError, "warning");
        } else {
          var _msg$toggleCredential;
          showToast(((_msg$toggleCredential = msg.toggleCredential) === null || _msg$toggleCredential === void 0 ? void 0 : _msg$toggleCredential.success) || 'Success', "success");
        }
      });
      checkSections();
      checkGroups();
    });
  });
}
function enableCredentialByStatus() {
  var credentialsByStatus = document.querySelectorAll('.lle-credential-checkbox-status-list');
  credentialsByStatus.forEach(function (credentialByStatus) {
    credentialByStatus.addEventListener('click', function () {
      var groupId = credentialByStatus.dataset.groupId;
      var credentialId = credentialByStatus.dataset.credentialId;
      apiCall('/admin/credential/allow-status/' + credentialId + '/' + groupId + '/' + (credentialByStatus.checked ? 1 : 0)).then(function (_ref4) {
        var ok = _ref4.ok,
          remoteError = _ref4.remoteError,
          error = _ref4.error;
        if (!ok) {
          credentialByStatus.checked = !credentialByStatus.checked;
          showToast(error, "danger");
          return;
        }
        var statusList = document.querySelector('.lle-credential-group-' + groupId + '-credential-' + credentialId + '-show-status');
        statusList.classList.toggle('d-none');
        if (remoteError) {
          showToast(remoteError, "warning");
        } else {
          var _msg$allowStatus;
          showToast(((_msg$allowStatus = msg.allowStatus) === null || _msg$allowStatus === void 0 ? void 0 : _msg$allowStatus.success) || 'Success', "success");
        }
      });
    });
  });
}
function checkCredentialByStatus() {
  var credentialStatusCheckboxes = document.querySelectorAll('.lle-credential-checkbox-group-credential-status');
  credentialStatusCheckboxes.forEach(function (credentialStatusCheckbox) {
    credentialStatusCheckbox.addEventListener('click', function () {
      var groupId = credentialStatusCheckbox.dataset.groupId;
      var credentialId = credentialStatusCheckbox.dataset.credentialId;
      var credentialStatus = credentialStatusCheckbox.dataset.credentialStatus;
      apiCall('/admin/credential/allow-for-status/' + credentialId + '/' + groupId + '/' + credentialStatus + '/' + (credentialStatusCheckbox.checked ? 1 : 0)).then(function (_ref5) {
        var ok = _ref5.ok,
          remoteError = _ref5.remoteError,
          error = _ref5.error;
        if (!ok) {
          credentialStatusCheckbox.checked = !credentialStatusCheckbox.checked;
          showToast(error, "danger");
          return;
        }
        if (remoteError) {
          showToast(remoteError, "warning");
        } else {
          var _msg$allowForStatus;
          showToast(((_msg$allowForStatus = msg.allowForStatus) === null || _msg$allowForStatus === void 0 ? void 0 : _msg$allowForStatus.success) || 'Success', "success");
        }
      });
    });
  });
}
function showToast(text, type) {
  var colors = {
    success: '#1CC88A',
    warning: '#F6C23E',
    danger: '#E74A3B'
  };
  toastify_js__WEBPACK_IMPORTED_MODULE_0___default()({
    text: text,
    duration: type === 'success' ? 1500 : 3000,
    style: {
      background: colors[type],
      padding: '15px 20px',
      fontSize: '17px'
    }
  }).showToast();
}
})();

/******/ })()
;