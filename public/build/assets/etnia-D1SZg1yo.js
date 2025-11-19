import{K as h,L as r,aE as v,V as g,c as d,o as c,e as m,F as u,k as I,p as y,l as k,t as b,I as w}from"./app-BxIIz8xi.js";import{s as T}from"./index-BpLu9pOF.js";var S=({dt:t})=>`
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
    border-top: 2px solid ${t("steps.separator.background")};
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
    transition: outline-color ${t("steps.transition.duration")}, box-shadow ${t("steps.transition.duration")};
    border-radius: ${t("steps.item.link.border.radius")};
    outline-color: transparent;
    gap: ${t("steps.item.link.gap")};
}

.p-steps-item-link:not(.p-disabled):focus-visible {
    box-shadow: ${t("steps.item.link.focus.ring.shadow")};
    outline: ${t("steps.item.link.focus.ring.width")} ${t("steps.item.link.focus.ring.style")} ${t("steps.item.link.focus.ring.color")};
    outline-offset: ${t("steps.item.link.focus.ring.offset")};
}

.p-steps-item-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    color: ${t("steps.item.label.color")};
    display: block;
    font-weight: ${t("steps.item.label.font.weight")};
}

.p-steps-item-number {
    display: flex;
    align-items: center;
    justify-content: center;
    color: ${t("steps.item.number.color")};
    border: 2px solid ${t("steps.item.number.border.color")};
    background: ${t("steps.item.number.background")};
    min-width: ${t("steps.item.number.size")};
    height: ${t("steps.item.number.size")};
    line-height: ${t("steps.item.number.size")};
    font-size: ${t("steps.item.number.font.size")};
    z-index: 1;
    border-radius: ${t("steps.item.number.border.radius")};
    position: relative;
    font-weight: ${t("steps.item.number.font.weight")};
}

.p-steps-item-number::after {
    content: " ";
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: ${t("steps.item.number.border.radius")};
    box-shadow: ${t("steps.item.number.shadow")};
}

.p-steps:not(.p-readonly) .p-steps-item {
    cursor: pointer;
}

.p-steps-item-active .p-steps-item-number {
    background: ${t("steps.item.number.active.background")};
    border-color: ${t("steps.item.number.active.border.color")};
    color: ${t("steps.item.number.active.color")};
}

.p-steps-item-active .p-steps-item-label {
    color: ${t("steps.item.label.active.color")};
}
`,$={root:function(e){var n=e.props;return["p-steps p-component",{"p-readonly":n.readonly}]},list:"p-steps-list",item:function(e){var n=e.instance,o=e.item,l=e.index;return["p-steps-item",{"p-steps-item-active":n.isActive(l),"p-disabled":n.isItemDisabled(o,l)}]},itemLink:"p-steps-item-link",itemNumber:"p-steps-item-number",itemLabel:"p-steps-item-label"},C=h.extend({name:"steps",style:S,classes:$}),P={name:"BaseSteps",extends:T,props:{id:{type:String},model:{type:Array,default:null},readonly:{type:Boolean,default:!0},activeStep:{type:Number,default:0}},style:C,provide:function(){return{$pcSteps:this,$parentInstance:this}}},x={name:"Steps",extends:P,inheritAttrs:!1,emits:["update:activeStep","step-change"],data:function(){return{d_activeStep:this.activeStep}},watch:{activeStep:function(e){this.d_activeStep=e}},mounted:function(){var e=this.findFirstItem();e&&(e.tabIndex="0")},methods:{getPTOptions:function(e,n,o){return this.ptm(e,{context:{item:n,index:o,active:this.isActive(o),disabled:this.isItemDisabled(n,o)}})},onItemClick:function(e,n,o){if(this.disabled(n)||this.readonly){e.preventDefault();return}n.command&&n.command({originalEvent:e,item:n}),o!==this.d_activeStep&&(this.d_activeStep=o,this.$emit("update:activeStep",this.d_activeStep)),this.$emit("step-change",{originalEvent:e,index:o})},onItemKeydown:function(e,n){switch(e.code){case"ArrowRight":{this.navigateToNextItem(e.target),e.preventDefault();break}case"ArrowLeft":{this.navigateToPrevItem(e.target),e.preventDefault();break}case"Home":{this.navigateToFirstItem(e.target),e.preventDefault();break}case"End":{this.navigateToLastItem(e.target),e.preventDefault();break}case"Tab":break;case"Enter":case"NumpadEnter":case"Space":{this.onItemClick(e,n),e.preventDefault();break}}},navigateToNextItem:function(e){var n=this.findNextItem(e);n&&this.setFocusToMenuitem(e,n)},navigateToPrevItem:function(e){var n=this.findPrevItem(e);n&&this.setFocusToMenuitem(e,n)},navigateToFirstItem:function(e){var n=this.findFirstItem(e);n&&this.setFocusToMenuitem(e,n)},navigateToLastItem:function(e){var n=this.findLastItem(e);n&&this.setFocusToMenuitem(e,n)},findNextItem:function(e){var n=e.parentElement.nextElementSibling;return n?n.children[0]:null},findPrevItem:function(e){var n=e.parentElement.previousElementSibling;return n?n.children[0]:null},findFirstItem:function(){var e=g(this.$refs.list,'[data-pc-section="item"]');return e?e.children[0]:null},findLastItem:function(){var e=v(this.$refs.list,'[data-pc-section="item"]');return e?e[e.length-1].children[0]:null},setFocusToMenuitem:function(e,n){e.tabIndex="-1",n.tabIndex="0",n.focus()},isActive:function(e){return e===this.d_activeStep},isItemDisabled:function(e,n){return this.disabled(e)||this.readonly&&!this.isActive(n)},visible:function(e){return typeof e.visible=="function"?e.visible():e.visible!==!1},disabled:function(e){return typeof e.disabled=="function"?e.disabled():e.disabled},label:function(e){return typeof e.label=="function"?e.label():e.label},getMenuItemProps:function(e,n){var o=this;return{action:r({class:this.cx("itemLink"),onClick:function(i){return o.onItemClick(i,e)},onKeyDown:function(i){return o.onItemKeydown(i,e)}},this.getPTOptions("itemLink",e,n)),step:r({class:this.cx("itemNumber")},this.getPTOptions("itemNumber",e,n)),label:r({class:this.cx("itemLabel")},this.getPTOptions("itemLabel",e,n))}}}},L=["id"],N=["aria-current","onClick","onKeydown","data-p-active","data-p-disabled"];function D(t,e,n,o,l,i){return c(),d("nav",r({id:t.id,class:t.cx("root")},t.ptmi("root")),[m("ol",r({ref:"list",class:t.cx("list")},t.ptm("list")),[(c(!0),d(u,null,I(t.model,function(s,a){return c(),d(u,{key:i.label(s)+"_"+a.toString()},[i.visible(s)?(c(),d("li",r({key:0,class:[t.cx("item",{item:s,index:a}),s.class],style:s.style,"aria-current":i.isActive(a)?"step":void 0,onClick:function(p){return i.onItemClick(p,s,a)},onKeydown:function(p){return i.onItemKeydown(p,s,a)},ref_for:!0},i.getPTOptions("item",s,a),{"data-p-active":i.isActive(a),"data-p-disabled":i.isItemDisabled(s,a)}),[t.$slots.item?(c(),k(w(t.$slots.item),{key:1,item:s,index:a,active:a===l.d_activeStep,label:i.label(s),props:i.getMenuItemProps(s,a)},null,8,["item","index","active","label","props"])):(c(),d("span",r({key:0,class:t.cx("itemLink"),ref_for:!0},i.getPTOptions("itemLink",s,a)),[m("span",r({class:t.cx("itemNumber"),ref_for:!0},i.getPTOptions("itemNumber",s,a)),b(a+1),17),m("span",r({class:t.cx("itemLabel"),ref_for:!0},i.getPTOptions("itemLabel",s,a)),b(i.label(s)),17)],16))],16,N)):y("",!0)],64)}),128))],16)],16,L)}x.render=D;const z=[{id:"0",nombre:"Tarjeta de Identidad",code:"TI"},{id:"2",nombre:"Cédula Ciudadanía",code:"CC"},{id:"3",nombre:"Cédula Extranjería",code:"CE"}],E=[{id:"0",nombre:"Sin condición",code:"NA"},{id:"1",nombre:"Persona con discapacidad",code:"discapacitado"},{id:"2",nombre:"Desplazados",code:"desplazados"},{id:"3",nombre:"Victimas",code:"victimasConfArm"},{id:"4",nombre:"Mujer cabeza de hogar",code:"mujerCabHogar"},{id:"5",nombre:"Padre cabeza de hogar",code:"hombreCabHogar"},{id:"6",nombre:"Habitante de calle",code:"habitanteCalle"},{id:"7",nombre:"Migrante",code:"migrante"}],M=[{id:"0",nombre:"No aplica",code:"NA"},{id:"1",nombre:"Mestizo",code:"mestizo"},{id:"2",nombre:"Afrocolombiano",code:"afro"},{id:"3",nombre:"Indígena",code:"indigena"},{id:"4",nombre:"Palanquero",code:"palanquero"},{id:"5",nombre:"Raizal",code:"raizal"},{id:"6",nombre:"ROM",code:"rom"}];export{E as c,M as e,x as s,z as t};
