import{a as o,b as l,f as d,H as c,G as u}from"./app-BqJagvOZ.js";import{s as p,c as f}from"./index-C_DrafIa.js";function S(e,t){return l(),o("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",fill:"currentColor","aria-hidden":"true","data-slot":"icon"},[d("path",{"fill-rule":"evenodd",d:"M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z","clip-rule":"evenodd"})])}function _(e,t){return l(),o("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",fill:"currentColor","aria-hidden":"true","data-slot":"icon"},[d("path",{"fill-rule":"evenodd",d:"M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z","clip-rule":"evenodd"})])}const B=(e,t)=>{const a=e.__vccOpts||e;for(const[r,s]of t)a[r]=s;return a};var x=({dt:e})=>`
.p-textarea {
    font-family: inherit;
    font-feature-settings: inherit;
    font-size: 1rem;
    color: ${e("textarea.color")};
    background: ${e("textarea.background")};
    padding-block: ${e("textarea.padding.y")};
    padding-inline: ${e("textarea.padding.x")};
    border: 1px solid ${e("textarea.border.color")};
    transition: background ${e("textarea.transition.duration")}, color ${e("textarea.transition.duration")}, border-color ${e("textarea.transition.duration")}, outline-color ${e("textarea.transition.duration")}, box-shadow ${e("textarea.transition.duration")};
    appearance: none;
    border-radius: ${e("textarea.border.radius")};
    outline-color: transparent;
    box-shadow: ${e("textarea.shadow")};
}

.p-textarea:enabled:hover {
    border-color: ${e("textarea.hover.border.color")};
}

.p-textarea:enabled:focus {
    border-color: ${e("textarea.focus.border.color")};
    box-shadow: ${e("textarea.focus.ring.shadow")};
    outline: ${e("textarea.focus.ring.width")} ${e("textarea.focus.ring.style")} ${e("textarea.focus.ring.color")};
    outline-offset: ${e("textarea.focus.ring.offset")};
}

.p-textarea.p-invalid {
    border-color: ${e("textarea.invalid.border.color")};
}

.p-textarea.p-variant-filled {
    background: ${e("textarea.filled.background")};
}

.p-textarea.p-variant-filled:enabled:hover {
    background: ${e("textarea.filled.hover.background")};
}

.p-textarea.p-variant-filled:enabled:focus {
    background: ${e("textarea.filled.focus.background")};
}

.p-textarea:disabled {
    opacity: 1;
    background: ${e("textarea.disabled.background")};
    color: ${e("textarea.disabled.color")};
}

.p-textarea::placeholder {
    color: ${e("textarea.placeholder.color")};
}

.p-textarea.p-invalid::placeholder {
    color: ${e("textarea.invalid.placeholder.color")};
}

.p-textarea-fluid {
    width: 100%;
}

.p-textarea-resizable {
    overflow: hidden;
    resize: none;
}

.p-textarea-sm {
    font-size: ${e("textarea.sm.font.size")};
    padding-block: ${e("textarea.sm.padding.y")};
    padding-inline: ${e("textarea.sm.padding.x")};
}

.p-textarea-lg {
    font-size: ${e("textarea.lg.font.size")};
    padding-block: ${e("textarea.lg.padding.y")};
    padding-inline: ${e("textarea.lg.padding.x")};
}
`,h={root:function(t){var a=t.instance,r=t.props;return["p-textarea p-component",{"p-filled":a.$filled,"p-textarea-resizable ":r.autoResize,"p-textarea-sm p-inputfield-sm":r.size==="small","p-textarea-lg p-inputfield-lg":r.size==="large","p-invalid":a.$invalid,"p-variant-filled":a.$variant==="filled","p-textarea-fluid":a.$fluid}]}},b=c.extend({name:"textarea",style:x,classes:h}),v={name:"BaseTextarea",extends:p,props:{autoResize:Boolean},style:b,provide:function(){return{$pcTextarea:this,$parentInstance:this}}};function i(e){"@babel/helpers - typeof";return i=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(t){return typeof t}:function(t){return t&&typeof Symbol=="function"&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},i(e)}function g(e,t,a){return(t=$(t))in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}function $(e){var t=m(e,"string");return i(t)=="symbol"?t:t+""}function m(e,t){if(i(e)!="object"||!e)return e;var a=e[Symbol.toPrimitive];if(a!==void 0){var r=a.call(e,t);if(i(r)!="object")return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return(t==="string"?String:Number)(e)}var y={name:"Textarea",extends:v,inheritAttrs:!1,observer:null,mounted:function(){var t=this;this.autoResize&&(this.observer=new ResizeObserver(function(){requestAnimationFrame(function(){t.resize()})}),this.observer.observe(this.$el))},updated:function(){this.autoResize&&this.resize()},beforeUnmount:function(){this.observer&&this.observer.disconnect()},methods:{resize:function(){this.$el.offsetParent&&(this.$el.style.height="auto",this.$el.style.height=this.$el.scrollHeight+"px",parseFloat(this.$el.style.height)>=parseFloat(this.$el.style.maxHeight)?(this.$el.style.overflowY="scroll",this.$el.style.height=this.$el.style.maxHeight):this.$el.style.overflow="hidden")},onInput:function(t){this.autoResize&&this.resize(),this.writeValue(t.target.value,t)}},computed:{attrs:function(){return u(this.ptmi("root",{context:{filled:this.$filled,disabled:this.disabled}}),this.formField)},dataP:function(){return f(g({invalid:this.$invalid,fluid:this.$fluid,filled:this.$variant==="filled"},this.size,this.size))}}},w=["value","name","disabled","aria-invalid","data-p"];function z(e,t,a,r,s,n){return l(),o("textarea",u({class:e.cx("root"),value:e.d_value,name:e.name,disabled:e.disabled,"aria-invalid":e.invalid||void 0,"data-p":n.dataP,onInput:t[0]||(t[0]=function(){return n.onInput&&n.onInput.apply(n,arguments)})},n.attrs),null,16,w)}y.render=z;export{B as _,S as a,_ as r,y as s};
