!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=8)}([function(e,t){e.exports=window.wp.element},function(e,t){e.exports=window.wp.data},function(e,t){e.exports=function(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t,n){var r=n(9),o=n(10),c=n(11),i=n(12);e.exports=function(e){return r(e)||o(e)||c(e)||i()},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t){e.exports=window.wp.components},function(e,t){e.exports=window.wp.hooks},function(e,t){e.exports=window.wp.i18n},,function(e,t,n){"use strict";n.r(t);var r=n(3),o=n.n(r),c=n(0),i=n(4),u=n(1),a=n(5),l=n(6);Object(a.addFilter)("editor.PostTaxonomyType","pfmc-feature-set/add-pinned-term-control",(function(e){return function(t){var n=t.slug,r=t.terms;if("council_meeting_connect"!==n)return Object(c.createElement)(e,t);var a=Object(u.withSelect)((function(e){return{pinnedTerms:e("core").getEntityRecords("taxonomy","council_meeting_connect",{meta_key:"_pinned",meta_value:1})}}))((function(e){var n=e.pinnedTerms;return n?Object(c.createElement)(c.Fragment,null,Object(c.createElement)("p",null,Object(l.__)("Pinned Terms")),Object(c.createElement)("div",{style:{margin:"-6px 0 1em -6px",maxHeight:"10.5em",overflow:"auto",padding:"6px 0 2px 6px"}},n.map((function(e){return Object(c.createElement)(i.CheckboxControl,{label:e.name,checked:t.terms&&t.terms.includes(e.id),onChange:function(){var t;t=r.includes(e.id)?r.filter((function(t){return t!==e.id})):[].concat(o()(r),[e.id]),Object(u.dispatch)("core/editor").editPost({council_meeting_connect:t})}})})))):null}));return Object(c.createElement)(c.Fragment,null,Object(c.createElement)(a,null),Object(c.createElement)(e,t))}}))},function(e,t,n){var r=n(2);e.exports=function(e){if(Array.isArray(e))return r(e)},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t){e.exports=function(e){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(e))return Array.from(e)},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t,n){var r=n(2);e.exports=function(e,t){if(e){if("string"==typeof e)return r(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?r(e,t):void 0}},e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t){e.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")},e.exports.default=e.exports,e.exports.__esModule=!0}]);