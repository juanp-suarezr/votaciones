import{s as u,c as g}from"./index-LU4V4PYX.js";import{B as d,c as i,o as s,j as p,m as c,x as y,y as o,C as f,b,t as m}from"./app-DNZf-PZV.js";var $=({dt:n})=>`
.p-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: ${n("tag.primary.background")};
    color: ${n("tag.primary.color")};
    font-size: ${n("tag.font.size")};
    font-weight: ${n("tag.font.weight")};
    padding: ${n("tag.padding")};
    border-radius: ${n("tag.border.radius")};
    gap: ${n("tag.gap")};
}

.p-tag-icon {
    font-size: ${n("tag.icon.size")};
    width: ${n("tag.icon.size")};
    height:${n("tag.icon.size")};
}

.p-tag-rounded {
    border-radius: ${n("tag.rounded.border.radius")};
}

.p-tag-success {
    background: ${n("tag.success.background")};
    color: ${n("tag.success.color")};
}

.p-tag-info {
    background: ${n("tag.info.background")};
    color: ${n("tag.info.color")};
}

.p-tag-warn {
    background: ${n("tag.warn.background")};
    color: ${n("tag.warn.color")};
}

.p-tag-danger {
    background: ${n("tag.danger.background")};
    color: ${n("tag.danger.color")};
}

.p-tag-secondary {
    background: ${n("tag.secondary.background")};
    color: ${n("tag.secondary.color")};
}

.p-tag-contrast {
    background: ${n("tag.contrast.background")};
    color: ${n("tag.contrast.color")};
}
`,v={root:function(e){var r=e.props;return["p-tag p-component",{"p-tag-info":r.severity==="info","p-tag-success":r.severity==="success","p-tag-warn":r.severity==="warn","p-tag-danger":r.severity==="danger","p-tag-secondary":r.severity==="secondary","p-tag-contrast":r.severity==="contrast","p-tag-rounded":r.rounded}]},icon:"p-tag-icon",label:"p-tag-label"},k=d.extend({name:"tag",style:$,classes:v}),h={name:"BaseTag",extends:u,props:{value:null,severity:null,rounded:Boolean,icon:String},style:k,provide:function(){return{$pcTag:this,$parentInstance:this}}};function t(n){"@babel/helpers - typeof";return t=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(e){return typeof e}:function(e){return e&&typeof Symbol=="function"&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},t(n)}function S(n,e,r){return(e=w(e))in n?Object.defineProperty(n,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):n[e]=r,n}function w(n){var e=P(n,"string");return t(e)=="symbol"?e:e+""}function P(n,e){if(t(n)!="object"||!n)return n;var r=n[Symbol.toPrimitive];if(r!==void 0){var a=r.call(n,e);if(t(a)!="object")return a;throw new TypeError("@@toPrimitive must return a primitive value.")}return(e==="string"?String:Number)(n)}var B={name:"Tag",extends:h,inheritAttrs:!1,computed:{dataP:function(){return g(S({rounded:this.rounded},this.severity,this.severity))}}},z=["data-p"];function j(n,e,r,a,T,l){return s(),i("span",o({class:n.cx("root"),"data-p":l.dataP},n.ptmi("root")),[n.$slots.icon?(s(),p(f(n.$slots.icon),o({key:0,class:n.cx("icon")},n.ptm("icon")),null,16,["class"])):n.icon?(s(),i("span",o({key:1,class:[n.cx("icon"),n.icon]},n.ptm("icon")),null,16)):c("",!0),n.value!=null||n.$slots.default?y(n.$slots,"default",{key:2},function(){return[b("span",o({class:n.cx("label")},n.ptm("label")),m(n.value),17)]}):c("",!0)],16,z)}B.render=j;export{B as s};
