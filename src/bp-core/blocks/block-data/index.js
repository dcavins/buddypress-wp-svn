(()=>{"use strict";var e={n:t=>{var r=t&&t.__esModule?()=>t.default:()=>t;return e.d(r,{a:r}),r},d:(t,r)=>{for(var n in r)e.o(r,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:r[n]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t),r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},t={};e.r(t),e.d(t,{activityTypes:()=>O,currentPostId:()=>E,default:()=>y,getCurrentWidgetsSidebar:()=>P,isActive:()=>b,loggedInUser:()=>T,postAuhor:()=>A});var r={};e.r(r),e.d(r,{getActiveComponents:()=>u});var n={};e.r(n),e.d(n,{fetchFromAPI:()=>l,getActiveComponents:()=>a});var o={};e.r(o),e.d(o,{getActiveComponents:()=>p});const s=window.lodash,c=window.wp.data,i="bp/core",u=e=>e.components||[],d={GET_ACTIVE_COMPONENTS:"GET_ACTIVE_COMPONENTS",FETCH_FROM_API:"FETCH_FROM_API"};function a(e){return{type:d.GET_ACTIVE_COMPONENTS,list:e}}function l(e,t){return{type:d.FETCH_FROM_API,path:e,parse:t}}function*p(){const e=yield l("/buddypress/v2/components?status=active",!0);yield a(e)}const f={components:[]},g=window.wp.apiFetch;var C=e.n(g);const v={FETCH_FROM_API:({path:e,parse:t})=>C()({path:e,parse:t})};(0,c.registerStore)(i,{reducer:(e=f,t)=>t.type===d.GET_ACTIVE_COMPONENTS?{...e,components:t.list}:e,actions:n,selectors:r,controls:v,resolvers:o});const _=i;function b(e,t=""){const r=(0,c.useSelect)((e=>e(_).getActiveComponents()),[]),n=(0,s.find)(r,["name",e]);return t?(0,s.get)(n,["features",t]):!!n}const y=b;function O(){const e=(0,c.useSelect)((e=>e(_).getActiveComponents()),[]),t=(0,s.find)(e,["name","activity"]);if(!t)return[];const r=(0,s.get)(t,["features","types"]);let n=[];return Object.entries(r).forEach((([e,t])=>{n.push({label:t,value:e})})),n}function T(){return(0,c.useSelect)((e=>e("core")?e("core").getCurrentUser():{}),[])}function A(){return(0,c.useSelect)((e=>{const t=e("core/editor"),r=e("core");if(t&&r){const e=t.getCurrentPostAttribute("author"),n=r.getAuthors();return(0,s.find)(n,["id",e])}return{}}),[])}function E(){return(0,c.useSelect)((e=>{const t=e("core/editor");return t?t.getCurrentPostId():0}),[])}function P(e=""){return(0,c.useSelect)((t=>{const r=t("core/block-editor"),n=t("core/edit-widgets");if(e&&n&&r){const t=r.getBlocks(),n=r.getBlockParents(e);let o=[];return t.forEach((({clientId:e,attributes:t})=>{o.push({id:t.id,isCurrent:-1!==n.indexOf(e)})})),(0,s.find)(o,["isCurrent",!0])}return{}}),[])}(window.bp=window.bp||{}).blockData=t})();