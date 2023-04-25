!function(){"use strict";var e={n:function(t){var r=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(r,{a:r}),r},d:function(t,r){for(var o in r)e.o(r,o)&&!e.o(t,o)&&Object.defineProperty(t,o,{enumerable:!0,get:r[o]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.blocks,r=window.wp.element,o=window.wp.blockEditor,s=window.wp.components,n=window.wp.i18n,l=window.wp.serverSideRender,a=e.n(l),d=window.bp.blockComponents,i=window.bp.blockData;const u=[{label:(0,n.__)("None","buddypress"),value:"none"},{label:(0,n.__)("Thumb","buddypress"),value:"thumb"},{label:(0,n.__)("Full","buddypress"),value:"full"}],p={public:(0,n.__)("Public","buddypress"),private:(0,n.__)("Private","buddypress"),hidden:(0,n.__)("Hidden","buddypress")},c=e=>e&&e.status&&p[e.status]?p[e.status]:null;var b=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"bp/group","title":"Group","category":"widgets","icon":"buddicons-groups","description":"BuddyPress Group.","keywords":["BuddyPress","group","community"],"textdomain":"buddypress","attributes":{"itemID":{"type":"integer","default":0},"avatarSize":{"type":"string","default":"full"},"displayDescription":{"type":"boolean","default":true},"displayActionButton":{"type":"boolean","default":true},"displayCoverImage":{"type":"boolean","default":true}},"supports":{"align":true},"editorScript":"file:index.js","style":"file:index.css"}');(0,t.registerBlockType)(b,{icon:{background:"#fff",foreground:"#d84800",src:"buddicons-groups"},edit:e=>{let{attributes:t,setAttributes:l}=e;const p=(0,o.useBlockProps)(),b=(0,i.isActive)("groups","avatar"),y=(0,i.isActive)("groups","cover"),{avatarSize:g,displayDescription:m,displayActionButton:_,displayCoverImage:h}=t;return t.itemID?(0,r.createElement)("div",p,(0,r.createElement)(o.BlockControls,null,(0,r.createElement)(s.Toolbar,{label:(0,n.__)("Block toolbar","buddypress")},(0,r.createElement)(s.ToolbarButton,{icon:"edit",title:(0,n.__)("Select another group","buddypress"),onClick:()=>{l({itemID:0})}}))),(0,r.createElement)(o.InspectorControls,null,(0,r.createElement)(s.PanelBody,{title:(0,n.__)("Settings","buddypress"),initialOpen:!0},(0,r.createElement)(s.ToggleControl,{label:(0,n.__)("Display Group's home button","buddypress"),checked:!!_,onChange:()=>{l({displayActionButton:!_})},help:_?(0,n.__)("Include a link to the group's home page under their name.","buddypress"):(0,n.__)("Toggle to display a link to the group's home page under their name.","buddypress")}),(0,r.createElement)(s.ToggleControl,{label:(0,n.__)("Display group's description","buddypress"),checked:!!m,onChange:()=>{l({displayDescription:!m})},help:m?(0,n.__)("Include the group's description under their name.","buddypress"):(0,n.__)("Toggle to display the group's description under their name.","buddypress")}),b&&(0,r.createElement)(s.SelectControl,{label:(0,n.__)("Avatar size","buddypress"),value:g,options:u,help:(0,n.__)('Select "None" to disable the avatar.',"buddypress"),onChange:e=>{l({avatarSize:e})}}),y&&(0,r.createElement)(s.ToggleControl,{label:(0,n.__)("Display Cover Image","buddypress"),checked:!!h,onChange:()=>{l({displayCoverImage:!h})},help:h?(0,n.__)("Include the group's cover image over their name.","buddypress"):(0,n.__)("Toggle to display the group's cover image over their name.","buddypress")}))),(0,r.createElement)(s.Disabled,null,(0,r.createElement)(a(),{block:"bp/group",attributes:t}))):(0,r.createElement)("div",p,(0,r.createElement)(s.Placeholder,{icon:"buddicons-groups",label:(0,n.__)("BuddyPress Group","buddypress"),instructions:(0,n.__)("Start typing the name of the group you want to feature into this post.","buddypress")},(0,r.createElement)(d.AutoCompleter,{component:"groups",objectQueryArgs:{show_hidden:!1},slugValue:c,ariaLabel:(0,n.__)("Group's name","buddypress"),placeholder:(0,n.__)("Enter Group's name here…","buddypress"),onSelectItem:l,useAvatar:b})))}})}();