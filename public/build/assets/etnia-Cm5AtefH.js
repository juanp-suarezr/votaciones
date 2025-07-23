import{C as b,B as a,a2 as v,a3 as k,a as c,b as l,f as d,F as u,h as y,m as I,i as $,t as f,L as w}from"./app-pUSYwlfZ.js";import{s as h}from"./index-CaOUrpF-.js";var S=({dt:e})=>`
.p-steps {
    position: relative;
}

.p-steps-list {
    padding: 0;
    margin: 0;
    list-style-type: none;
    display: flex;
}

.p-steps-item {
    position: relative;
    display: flex;
    justify-content: center;
    flex: 1 1 auto;
}

.p-steps-item.p-disabled,
.p-steps-item.p-disabled * {
    opacity: 1;
    pointer-events: auto;
    user-select: auto;
    cursor: auto;
}

.p-steps-item:before {
    content: " ";
    border-top: 2px solid ${e("steps.separator.background")};
    width: 100%;
    top: 50%;
    left: 0;
    display: block;
    position: absolute;
    margin-top: calc(-1rem + 1px);
}

.p-steps-item:first-child::before {
    width: calc(50% + 1rem);
    transform: translateX(100%);
}

.p-steps-item:last-child::before {
    width: 50%;
}

.p-steps-item-link {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
    text-decoration: none;
    transition: outline-color ${e("steps.transition.duration")}, box-shadow ${e("steps.transition.duration")};
    border-radius: ${e("steps.item.link.border.radius")};
    outline-color: transparent;
    gap: ${e("steps.item.link.gap")};
}

.p-steps-item-link:not(.p-disabled):focus-visible {
    box-shadow: ${e("steps.item.link.focus.ring.shadow")};
    outline: ${e("steps.item.link.focus.ring.width")} ${e("steps.item.link.focus.ring.style")} ${e("steps.item.link.focus.ring.color")};
    outline-offset: ${e("steps.item.link.focus.ring.offset")};
}

.p-steps-item-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    color: ${e("steps.item.label.color")};
    display: block;
    font-weight: ${e("steps.item.label.font.weight")};
}

.p-steps-item-number {
    display: flex;
    align-items: center;
    justify-content: center;
    color: ${e("steps.item.number.color")};
    border: 2px solid ${e("steps.item.number.border.color")};
    background: ${e("steps.item.number.background")};
    min-width: ${e("steps.item.number.size")};
    height: ${e("steps.item.number.size")};
    line-height: ${e("steps.item.number.size")};
    font-size: ${e("steps.item.number.font.size")};
    z-index: 1;
    border-radius: ${e("steps.item.number.border.radius")};
    position: relative;
    font-weight: ${e("steps.item.number.font.weight")};
}

.p-steps-item-number::after {
    content: " ";
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: ${e("steps.item.number.border.radius")};
    box-shadow: ${e("steps.item.number.shadow")};
}

.p-steps:not(.p-readonly) .p-steps-item {
    cursor: pointer;
}

.p-steps-item-active .p-steps-item-number {
    background: ${e("steps.item.number.active.background")};
    border-color: ${e("steps.item.number.active.border.color")};
    color: ${e("steps.item.number.active.color")};
}

.p-steps-item-active .p-steps-item-label {
    color: ${e("steps.item.label.active.color")};
}
`,T={root:function(t){var n=t.props;return["p-steps p-component",{"p-readonly":n.readonly}]},list:"p-steps-list",item:function(t){var n=t.instance,o=t.item,p=t.index;return["p-steps-item",{"p-steps-item-active":n.isActive(p),"p-disabled":n.isItemDisabled(o,p)}]},itemLink:"p-steps-item-link",itemNumber:"p-steps-item-number",itemLabel:"p-steps-item-label"},P=b.extend({name:"steps",style:S,classes:T}),x={name:"BaseSteps",extends:h,props:{id:{type:String},model:{type:Array,default:null},readonly:{type:Boolean,default:!0},activeStep:{type:Number,default:0}},style:P,provide:function(){return{$pcSteps:this,$parentInstance:this}}},C={name:"Steps",extends:x,inheritAttrs:!1,emits:["update:activeStep","step-change"],data:function(){return{d_activeStep:this.activeStep}},watch:{activeStep:function(t){this.d_activeStep=t}},mounted:function(){var t=this.findFirstItem();t&&(t.tabIndex="0")},methods:{getPTOptions:function(t,n,o){return this.ptm(t,{context:{item:n,index:o,active:this.isActive(o),disabled:this.isItemDisabled(n,o)}})},onItemClick:function(t,n,o){if(this.disabled(n)||this.readonly){t.preventDefault();return}n.command&&n.command({originalEvent:t,item:n}),o!==this.d_activeStep&&(this.d_activeStep=o,this.$emit("update:activeStep",this.d_activeStep)),this.$emit("step-change",{originalEvent:t,index:o})},onItemKeydown:function(t,n){switch(t.code){case"ArrowRight":{this.navigateToNextItem(t.target),t.preventDefault();break}case"ArrowLeft":{this.navigateToPrevItem(t.target),t.preventDefault();break}case"Home":{this.navigateToFirstItem(t.target),t.preventDefault();break}case"End":{this.navigateToLastItem(t.target),t.preventDefault();break}case"Tab":break;case"Enter":case"NumpadEnter":case"Space":{this.onItemClick(t,n),t.preventDefault();break}}},navigateToNextItem:function(t){var n=this.findNextItem(t);n&&this.setFocusToMenuitem(t,n)},navigateToPrevItem:function(t){var n=this.findPrevItem(t);n&&this.setFocusToMenuitem(t,n)},navigateToFirstItem:function(t){var n=this.findFirstItem(t);n&&this.setFocusToMenuitem(t,n)},navigateToLastItem:function(t){var n=this.findLastItem(t);n&&this.setFocusToMenuitem(t,n)},findNextItem:function(t){var n=t.parentElement.nextElementSibling;return n?n.children[0]:null},findPrevItem:function(t){var n=t.parentElement.previousElementSibling;return n?n.children[0]:null},findFirstItem:function(){var t=k(this.$refs.list,'[data-pc-section="item"]');return t?t.children[0]:null},findLastItem:function(){var t=v(this.$refs.list,'[data-pc-section="item"]');return t?t[t.length-1].children[0]:null},setFocusToMenuitem:function(t,n){t.tabIndex="-1",n.tabIndex="0",n.focus()},isActive:function(t){return t===this.d_activeStep},isItemDisabled:function(t,n){return this.disabled(t)||this.readonly&&!this.isActive(n)},visible:function(t){return typeof t.visible=="function"?t.visible():t.visible!==!1},disabled:function(t){return typeof t.disabled=="function"?t.disabled():t.disabled},label:function(t){return typeof t.label=="function"?t.label():t.label},getMenuItemProps:function(t,n){var o=this;return{action:a({class:this.cx("itemLink"),onClick:function(s){return o.onItemClick(s,t)},onKeyDown:function(s){return o.onItemKeydown(s,t)}},this.getPTOptions("itemLink",t,n)),step:a({class:this.cx("itemNumber")},this.getPTOptions("itemNumber",t,n)),label:a({class:this.cx("itemLabel")},this.getPTOptions("itemLabel",t,n))}}}},L=["id"],D=["aria-current","onClick","onKeydown","data-p-active","data-p-disabled"];function N(e,t,n,o,p,s){return l(),c("nav",a({id:e.id,class:e.cx("root")},e.ptmi("root")),[d("ol",a({ref:"list",class:e.cx("list")},e.ptm("list")),[(l(!0),c(u,null,y(e.model,function(i,r){return l(),c(u,{key:s.label(i)+"_"+r.toString()},[s.visible(i)?(l(),c("li",a({key:0,class:[e.cx("item",{item:i,index:r}),i.class],style:i.style,"aria-current":s.isActive(r)?"step":void 0,onClick:function(m){return s.onItemClick(m,i,r)},onKeydown:function(m){return s.onItemKeydown(m,i,r)},ref_for:!0},s.getPTOptions("item",i,r),{"data-p-active":s.isActive(r),"data-p-disabled":s.isItemDisabled(i,r)}),[e.$slots.item?(l(),$(w(e.$slots.item),{key:1,item:i,index:r,active:r===p.d_activeStep,label:s.label(i),props:s.getMenuItemProps(i,r)},null,8,["item","index","active","label","props"])):(l(),c("span",a({key:0,class:e.cx("itemLink"),ref_for:!0},s.getPTOptions("itemLink",i,r)),[d("span",a({class:e.cx("itemNumber"),ref_for:!0},s.getPTOptions("itemNumber",i,r)),f(r+1),17),d("span",a({class:e.cx("itemLabel"),ref_for:!0},s.getPTOptions("itemLabel",i,r)),f(s.label(i)),17)],16))],16,D)):I("",!0)],64)}),128))],16)],16,L)}C.render=N;var A=({dt:e})=>`
.p-progressspinner {
    position: relative;
    margin: 0 auto;
    width: 100px;
    height: 100px;
    display: inline-block;
}

.p-progressspinner::before {
    content: "";
    display: block;
    padding-top: 100%;
}

.p-progressspinner-spin {
    height: 100%;
    transform-origin: center center;
    width: 100%;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin: auto;
    animation: p-progressspinner-rotate 2s linear infinite;
}

.p-progressspinner-circle {
    stroke-dasharray: 89, 200;
    stroke-dashoffset: 0;
    stroke: ${e("progressspinner.colorOne")};
    animation: p-progressspinner-dash 1.5s ease-in-out infinite, p-progressspinner-color 6s ease-in-out infinite;
    stroke-linecap: round;
}

@keyframes p-progressspinner-rotate {
    100% {
        transform: rotate(360deg);
    }
}
@keyframes p-progressspinner-dash {
    0% {
        stroke-dasharray: 1, 200;
        stroke-dashoffset: 0;
    }
    50% {
        stroke-dasharray: 89, 200;
        stroke-dashoffset: -35px;
    }
    100% {
        stroke-dasharray: 89, 200;
        stroke-dashoffset: -124px;
    }
}
@keyframes p-progressspinner-color {
    100%,
    0% {
        stroke: ${e("progressspinner.colorOne")};
    }
    40% {
        stroke: ${e("progressspinner.colorTwo")};
    }
    66% {
        stroke: ${e("progressspinner.colorThree")};
    }
    80%,
    90% {
        stroke: ${e("progressspinner.colorFour")};
    }
}
`,F={root:"p-progressspinner",spin:"p-progressspinner-spin",circle:"p-progressspinner-circle"},z=b.extend({name:"progressspinner",style:A,classes:F}),M={name:"BaseProgressSpinner",extends:h,props:{strokeWidth:{type:String,default:"2"},fill:{type:String,default:"none"},animationDuration:{type:String,default:"2s"}},style:z,provide:function(){return{$pcProgressSpinner:this,$parentInstance:this}}},E={name:"ProgressSpinner",extends:M,inheritAttrs:!1,computed:{svgStyle:function(){return{"animation-duration":this.animationDuration}}}},O=["fill","stroke-width"];function B(e,t,n,o,p,s){return l(),c("div",a({class:e.cx("root"),role:"progressbar"},e.ptmi("root")),[(l(),c("svg",a({class:e.cx("spin"),viewBox:"25 25 50 50",style:s.svgStyle},e.ptm("spin")),[d("circle",a({class:e.cx("circle"),cx:"50",cy:"50",r:"20",fill:e.fill,"stroke-width":e.strokeWidth,strokeMiterlimit:"10"},e.ptm("circle")),null,16,O)],16))],16)}E.render=B;const H=[{id:"0",nombre:"Tarjeta de Identidad",code:"TI"},{id:"2",nombre:"Cédula Ciudadanía",code:"CC"},{id:"3",nombre:"Cédula Extranjería",code:"CE"}],R=[{id:"0",nombre:"Sin condición",code:"NA"},{id:"1",nombre:"Persona con discapacidad",code:"discapacitado"},{id:"2",nombre:"Desplazados",code:"desplazados"},{id:"3",nombre:"Victimas",code:"victimasConfArm"},{id:"4",nombre:"Mujer cabeza de hogar",code:"mujerCabHogar"},{id:"5",nombre:"Padre cabeza de hogar",code:"hombreCabHogar"},{id:"6",nombre:"Habitante de calle",code:"habitanteCalle"},{id:"7",nombre:"Migrante",code:"migrante"}],V=[{id:"0",nombre:"No aplica",code:"NA"},{id:"1",nombre:"Mestizo",code:"mestizo"},{id:"2",nombre:"Afrocolombiano",code:"afro"},{id:"3",nombre:"Indígena",code:"indigena"},{id:"4",nombre:"Palanquero",code:"palanquero"},{id:"5",nombre:"Raizal",code:"raizal"},{id:"6",nombre:"ROM",code:"rom"}];export{E as a,R as c,V as e,C as s,H as t};
