!function(t,e){"object"==typeof exports&&"object"==typeof module?module.exports=e():"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?exports.CKEditor5=e():(t.CKEditor5=t.CKEditor5||{},t.CKEditor5.drupalMentionUser=e())}(self,(()=>(()=>{var t={"ckeditor5/src/core.js":(t,e,r)=>{t.exports=r("dll-reference CKEditor5.dll")("./src/core.js")},"dll-reference CKEditor5.dll":t=>{"use strict";t.exports=CKEditor5.dll}},e={};function r(i){var n=e[i];if(void 0!==n)return n.exports;var o=e[i]={exports:{}};return t[i](o,o.exports,r),o.exports}r.d=(t,e)=>{for(var i in e)r.o(e,i)&&!r.o(t,i)&&Object.defineProperty(t,i,{enumerable:!0,get:e[i]})},r.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e);var i={};return(()=>{"use strict";r.d(i,{default:()=>n});var t=r("ckeditor5/src/core.js");class e extends t.Plugin{init(){const t=this.editor,e={},r={};t.config.get("mention").feeds.forEach((t=>{t.drupalMentionsType&&t.marker&&(e[t.drupalMentionsType]=t.marker,r[t.marker]=t.drupalMentionsType)})),t.conversion.for("upcast").elementToAttribute({view:{name:"a",key:"data-mention",classes:"mention",attributes:{href:!0,"data-plugin":!0}},model:{key:"mention",value:r=>{let i=r.getAttribute("data-mention"),n=r.getAttribute("data-plugin"),o=e[n]??!1;null!==n&&null!==i&&o&&i.charAt(0)!==o&&r._setAttribute("data-mention",o+i);return t.plugins.get("Mention").toMentionAttribute(r,{link:r.getAttribute("href"),entity_type:r.getAttribute("data-entity-type"),entity_uuid:r.getAttribute("data-entity-uuid"),mention_uuid:r.getAttribute("data-mention-uuid"),plugin:r.getAttribute("data-plugin")})}},converterPriority:"high"}),t.conversion.for("downcast").attributeToElement({model:"mention",view:(t,{writer:e})=>{if(t)return e.createAttributeElement("a",{class:"mention","data-mention":t.id,"data-mention-uuid":t.mention_uuid??null,"data-entity-type":t.entity_type??null,"data-entity-uuid":t.entity_uuid??null,"data-plugin":t.plugin??null,href:t.link??null},{priority:20,id:t.uid})},converterPriority:"high"})}static get pluginName(){return"drupalMentionUser"}}const n={DrupalMentionUser:e}})(),i=i.default})()));