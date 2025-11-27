import{K as N,S as j,a as J,f as O,c as g,o as c,e as s,H as P,n as I,p as q,L as V,t as $,X as C,ad as B,A as Q,F as k,k as L,l as ee,af as te,d as w,x as oe,r as _,T as ne,w as U,q as le,b as i,u as a,Z as se,j as h,B as E,v as R}from"./app-DMmjur8u.js";import{_ as re}from"./AuthenticatedLayout-teFU9RHs.js";import{_ as v}from"./InputError-CVAg1U4j.js";import{_ as y}from"./InputLabel-D6LXdD6P.js";import{_ as D}from"./PrimaryButton-d7QaNbPD.js";import{_ as S}from"./TextInput-DIRcqpXU.js";import{s as ae}from"./index-RNATG-mG.js";import{_ as ie}from"./SecondaryLink-DLsg09bR.js";import{s as K,c as F}from"./index-Cv7L5PHJ.js";import{R as M}from"./index-B3Z6S0xV.js";import{s as ue}from"./index-kQbLMyEH.js";import{s as de}from"./index-B9lr-5W2.js";import{_ as ce}from"./SecondaryButton-CSoHuwTr.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./BreadCrumb-CVZ8DbVp.js";import"./SecondaryButtonReturn-DWTO2dr7.js";import"./index-DDF4SNcS.js";import"./XCircleIcon-B0kQxruv.js";import"./index-DbGOHdbB.js";import"./index-CU8bIpv3.js";import"./index-PL6f817z.js";import"./index-BSf0CH3f.js";import"./index-CtjTcf0t.js";var ge=({dt:e})=>`
.p-togglebutton {
    display: inline-flex;
    cursor: pointer;
    user-select: none;
    overflow: hidden;
    position: relative;
    color: ${e("togglebutton.color")};
    background: ${e("togglebutton.background")};
    border: 1px solid ${e("togglebutton.border.color")};
    padding: ${e("togglebutton.padding")};
    font-size: 1rem;
    font-family: inherit;
    font-feature-settings: inherit;
    transition: background ${e("togglebutton.transition.duration")}, color ${e("togglebutton.transition.duration")}, border-color ${e("togglebutton.transition.duration")},
        outline-color ${e("togglebutton.transition.duration")}, box-shadow ${e("togglebutton.transition.duration")};
    border-radius: ${e("togglebutton.border.radius")};
    outline-color: transparent;
    font-weight: ${e("togglebutton.font.weight")};
}

.p-togglebutton-content {
    display: inline-flex;
    flex: 1 1 auto;
    align-items: center;
    justify-content: center;
    gap: ${e("togglebutton.gap")};
    padding: ${e("togglebutton.content.padding")};
    background: transparent;
    border-radius: ${e("togglebutton.content.border.radius")};
    transition: background ${e("togglebutton.transition.duration")}, color ${e("togglebutton.transition.duration")}, border-color ${e("togglebutton.transition.duration")},
            outline-color ${e("togglebutton.transition.duration")}, box-shadow ${e("togglebutton.transition.duration")};
}

.p-togglebutton:not(:disabled):not(.p-togglebutton-checked):hover {
    background: ${e("togglebutton.hover.background")};
    color: ${e("togglebutton.hover.color")};
}

.p-togglebutton.p-togglebutton-checked {
    background: ${e("togglebutton.checked.background")};
    border-color: ${e("togglebutton.checked.border.color")};
    color: ${e("togglebutton.checked.color")};
}

.p-togglebutton-checked .p-togglebutton-content {
    background: ${e("togglebutton.content.checked.background")};
    box-shadow: ${e("togglebutton.content.checked.shadow")};
}

.p-togglebutton:focus-visible {
    box-shadow: ${e("togglebutton.focus.ring.shadow")};
    outline: ${e("togglebutton.focus.ring.width")} ${e("togglebutton.focus.ring.style")} ${e("togglebutton.focus.ring.color")};
    outline-offset: ${e("togglebutton.focus.ring.offset")};
}

.p-togglebutton.p-invalid {
    border-color: ${e("togglebutton.invalid.border.color")};
}

.p-togglebutton:disabled {
    opacity: 1;
    cursor: default;
    background: ${e("togglebutton.disabled.background")};
    border-color: ${e("togglebutton.disabled.border.color")};
    color: ${e("togglebutton.disabled.color")};
}

.p-togglebutton-label,
.p-togglebutton-icon {
    position: relative;
    transition: none;
}

.p-togglebutton-icon {
    color: ${e("togglebutton.icon.color")};
}

.p-togglebutton:not(:disabled):not(.p-togglebutton-checked):hover .p-togglebutton-icon {
    color: ${e("togglebutton.icon.hover.color")};
}

.p-togglebutton.p-togglebutton-checked .p-togglebutton-icon {
    color: ${e("togglebutton.icon.checked.color")};
}

.p-togglebutton:disabled .p-togglebutton-icon {
    color: ${e("togglebutton.icon.disabled.color")};
}

.p-togglebutton-sm {
    padding: ${e("togglebutton.sm.padding")};
    font-size: ${e("togglebutton.sm.font.size")};
}

.p-togglebutton-sm .p-togglebutton-content {
    padding: ${e("togglebutton.content.sm.padding")};
}

.p-togglebutton-lg {
    padding: ${e("togglebutton.lg.padding")};
    font-size: ${e("togglebutton.lg.font.size")};
}

.p-togglebutton-lg .p-togglebutton-content {
    padding: ${e("togglebutton.content.lg.padding")};
}
`,pe={root:function(o){var l=o.instance,d=o.props;return["p-togglebutton p-component",{"p-togglebutton-checked":l.active,"p-invalid":l.$invalid,"p-togglebutton-sm p-inputfield-sm":d.size==="small","p-togglebutton-lg p-inputfield-lg":d.size==="large"}]},content:"p-togglebutton-content",icon:"p-togglebutton-icon",label:"p-togglebutton-label"},be=N.extend({name:"togglebutton",style:ge,classes:pe}),fe={name:"BaseToggleButton",extends:K,props:{onIcon:String,offIcon:String,onLabel:{type:String,default:"Yes"},offLabel:{type:String,default:"No"},iconPos:{type:String,default:"left"},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null},size:{type:String,default:null}},style:be,provide:function(){return{$pcToggleButton:this,$parentInstance:this}}};function A(e){"@babel/helpers - typeof";return A=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(o){return typeof o}:function(o){return o&&typeof Symbol=="function"&&o.constructor===Symbol&&o!==Symbol.prototype?"symbol":typeof o},A(e)}function me(e,o,l){return(o=ve(o))in e?Object.defineProperty(e,o,{value:l,enumerable:!0,configurable:!0,writable:!0}):e[o]=l,e}function ve(e){var o=ye(e,"string");return A(o)=="symbol"?o:o+""}function ye(e,o){if(A(e)!="object"||!e)return e;var l=e[Symbol.toPrimitive];if(l!==void 0){var d=l.call(e,o);if(A(d)!="object")return d;throw new TypeError("@@toPrimitive must return a primitive value.")}return(o==="string"?String:Number)(e)}var H={name:"ToggleButton",extends:fe,inheritAttrs:!1,emits:["change"],methods:{getPTOptions:function(o){var l=o==="root"?this.ptmi:this.ptm;return l(o,{context:{active:this.active,disabled:this.disabled}})},onChange:function(o){!this.disabled&&!this.readonly&&(this.writeValue(!this.d_value,o),this.$emit("change",o))},onBlur:function(o){var l,d;(l=(d=this.formField).onBlur)===null||l===void 0||l.call(d,o)}},computed:{active:function(){return this.d_value===!0},hasLabel:function(){return j(this.onLabel)&&j(this.offLabel)},label:function(){return this.hasLabel?this.d_value?this.onLabel:this.offLabel:" "},dataP:function(){return F(me({checked:this.active,invalid:this.$invalid},this.size,this.size))}},directives:{ripple:M}},he=["tabindex","disabled","aria-pressed","aria-label","aria-labelledby","data-p-checked","data-p-disabled","data-p"],xe=["data-p"];function we(e,o,l,d,f,t){var p=J("ripple");return O((c(),g("button",V({type:"button",class:e.cx("root"),tabindex:e.tabindex,disabled:e.disabled,"aria-pressed":e.d_value,onClick:o[0]||(o[0]=function(){return t.onChange&&t.onChange.apply(t,arguments)}),onBlur:o[1]||(o[1]=function(){return t.onBlur&&t.onBlur.apply(t,arguments)})},t.getPTOptions("root"),{"aria-label":e.ariaLabel,"aria-labelledby":e.ariaLabelledby,"data-p-checked":t.active,"data-p-disabled":e.disabled,"data-p":t.dataP}),[s("span",V({class:e.cx("content")},t.getPTOptions("content"),{"data-p":t.dataP}),[P(e.$slots,"default",{},function(){return[P(e.$slots,"icon",{value:e.d_value,class:I(e.cx("icon"))},function(){return[e.onIcon||e.offIcon?(c(),g("span",V({key:0,class:[e.cx("icon"),e.d_value?e.onIcon:e.offIcon]},t.getPTOptions("icon")),null,16)):q("",!0)]}),s("span",V({class:e.cx("label")},t.getPTOptions("label")),$(t.label),17)]})],16,xe)],16,he)),[[p]])}H.render=we;var $e=({dt:e})=>`
.p-selectbutton {
    display: inline-flex;
    user-select: none;
    vertical-align: bottom;
    outline-color: transparent;
    border-radius: ${e("selectbutton.border.radius")};
}

.p-selectbutton .p-togglebutton {
    border-radius: 0;
    border-width: 1px 1px 1px 0;
}

.p-selectbutton .p-togglebutton:focus-visible {
    position: relative;
    z-index: 1;
}

.p-selectbutton .p-togglebutton:first-child {
    border-inline-start-width: 1px;
    border-start-start-radius: ${e("selectbutton.border.radius")};
    border-end-start-radius: ${e("selectbutton.border.radius")};
}

.p-selectbutton .p-togglebutton:last-child {
    border-start-end-radius: ${e("selectbutton.border.radius")};
    border-end-end-radius: ${e("selectbutton.border.radius")};
}

.p-selectbutton.p-invalid {
    outline: 1px solid ${e("selectbutton.invalid.border.color")};
    outline-offset: 0;
}
`,Se={root:function(o){var l=o.instance;return["p-selectbutton p-component",{"p-invalid":l.$invalid}]}},ke=N.extend({name:"selectbutton",style:$e,classes:Se}),Ve={name:"BaseSelectButton",extends:K,props:{options:Array,optionLabel:null,optionValue:null,optionDisabled:null,multiple:Boolean,allowEmpty:{type:Boolean,default:!0},dataKey:null,ariaLabelledby:{type:String,default:null},size:{type:String,default:null}},style:ke,provide:function(){return{$pcSelectButton:this,$parentInstance:this}}};function Oe(e,o){var l=typeof Symbol<"u"&&e[Symbol.iterator]||e["@@iterator"];if(!l){if(Array.isArray(e)||(l=G(e))||o){l&&(e=l);var d=0,f=function(){};return{s:f,n:function(){return d>=e.length?{done:!0}:{done:!1,value:e[d++]}},e:function(x){throw x},f}}throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}var t,p=!0,u=!1;return{s:function(){l=l.call(e)},n:function(){var x=l.next();return p=x.done,x},e:function(x){u=!0,t=x},f:function(){try{p||l.return==null||l.return()}finally{if(u)throw t}}}}function Le(e){return _e(e)||Be(e)||G(e)||Ae()}function Ae(){throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function G(e,o){if(e){if(typeof e=="string")return T(e,o);var l={}.toString.call(e).slice(8,-1);return l==="Object"&&e.constructor&&(l=e.constructor.name),l==="Map"||l==="Set"?Array.from(e):l==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(l)?T(e,o):void 0}}function Be(e){if(typeof Symbol<"u"&&e[Symbol.iterator]!=null||e["@@iterator"]!=null)return Array.from(e)}function _e(e){if(Array.isArray(e))return T(e)}function T(e,o){(o==null||o>e.length)&&(o=e.length);for(var l=0,d=Array(o);l<o;l++)d[l]=e[l];return d}var W={name:"SelectButton",extends:Ve,inheritAttrs:!1,emits:["change"],methods:{getOptionLabel:function(o){return this.optionLabel?B(o,this.optionLabel):o},getOptionValue:function(o){return this.optionValue?B(o,this.optionValue):o},getOptionRenderKey:function(o){return this.dataKey?B(o,this.dataKey):this.getOptionLabel(o)},isOptionDisabled:function(o){return this.optionDisabled?B(o,this.optionDisabled):!1},isOptionReadonly:function(o){if(this.allowEmpty)return!1;var l=this.isSelected(o);return this.multiple?l&&this.d_value.length===1:l},onOptionSelect:function(o,l,d){var f=this;if(!(this.disabled||this.isOptionDisabled(l)||this.isOptionReadonly(l))){var t=this.isSelected(l),p=this.getOptionValue(l),u;if(this.multiple)if(t){if(u=this.d_value.filter(function(m){return!C(m,p,f.equalityKey)}),!this.allowEmpty&&u.length===0)return}else u=this.d_value?[].concat(Le(this.d_value),[p]):[p];else{if(t&&!this.allowEmpty)return;u=t?null:p}this.writeValue(u,o),this.$emit("change",{event:o,value:u})}},isSelected:function(o){var l=!1,d=this.getOptionValue(o);if(this.multiple){if(this.d_value){var f=Oe(this.d_value),t;try{for(f.s();!(t=f.n()).done;){var p=t.value;if(C(p,d,this.equalityKey)){l=!0;break}}}catch(u){f.e(u)}finally{f.f()}}}else l=C(this.d_value,d,this.equalityKey);return l}},computed:{equalityKey:function(){return this.optionValue?null:this.dataKey},dataP:function(){return F({invalid:this.$invalid})}},directives:{ripple:M},components:{ToggleButton:H}},Re=["aria-labelledby","data-p"];function ze(e,o,l,d,f,t){var p=Q("ToggleButton");return c(),g("div",V({class:e.cx("root"),role:"group","aria-labelledby":e.ariaLabelledby},e.ptmi("root"),{"data-p":t.dataP}),[(c(!0),g(k,null,L(e.options,function(u,m){return c(),ee(p,{key:t.getOptionRenderKey(u),modelValue:t.isSelected(u),onLabel:t.getOptionLabel(u),offLabel:t.getOptionLabel(u),disabled:e.disabled||t.isOptionDisabled(u),unstyled:e.unstyled,size:e.size,readonly:t.isOptionReadonly(u),onChange:function(z){return t.onOptionSelect(z,u,m)},pt:e.ptm("pcToggleButton")},te({_:2},[e.$slots.option?{name:"default",fn:w(function(){return[P(e.$slots,"option",{option:u,index:m},function(){return[s("span",V({ref_for:!0},e.ptm("pcToggleButton").label),$(t.getOptionLabel(u)),17)]})]}),key:"0"}:void 0]),1032,["modelValue","onLabel","offLabel","disabled","unstyled","size","readonly","onChange","pt"])}),128))],16,Re)}W.render=ze;const Ce={class:"md:grid grid-cols-2 gap-4"},Pe={class:"flex flex-col bg-white border shadow-sm rounded-xl"},Ie={class:"bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 grid grid-cols-2 gap-4"},Te={class:"text-right"},je={class:"p-4 md:p-5"},Ue=["value"],Ee={key:1,class:"text-xs text-gray-600"},De=["href"],Ne=["value"],qe={key:1,class:"text-xs text-gray-600"},Ke=["href"],Fe={class:"col-span-2 w-full"},Me={class:"w-auto"},He={class:"w-full h-full mt-2"},Ge={class:"flex flex-wrap items-center w-auto gap-4"},We={key:0,class:"px-2 py-4 w-full border border-gray-400 rounded-md shadow-md mt-4"},Xe={class:"mt-4"},Ye={class:"card flex justify-content-center"},Ze={class:"col-span-2 w-full"},Je={class:"card flex z-10 gap-4 mt-4"},Qe={class:"card flex justify-content-center"},et={class:"col-span-2 w-full"},tt={class:"mt-4 flex flex-col items-center"},ot={class:"w-full bg-white border shadow-sm rounded-xl"},nt={class:"w-full h-full flex px-4"},lt={class:"mt-6 w-full"},st=["href"],rt={class:"mt-2"},at=["value"],it={key:1,class:"text-xs text-gray-600"},ut=["href"],dt={class:"mt-2"},ct=["value"],gt={key:1,class:"text-xs text-gray-600"},pt=["href"],bt={class:"mt-12 flex flex-col items-end"},Ut={__name:"Add",props:{roles:{type:Object,default:()=>({})},tipos:{type:Object,default:()=>({})},eventos:{type:Object,default:()=>({})},parametros:{type:Array,default:()=>[]},subtipos:{type:Array,default:()=>[]},numRegistrosInsertados:{type:Object,default:()=>({})},numRegistrosActualizados:{type:Object,default:()=>({})}},setup(e){const o=oe("$swal"),l=e,d=_("Usuario"),f=_(["Candidato","Usuario"]),t=ne({name:"",email:"",identificacion:"",tipo:"",password:"",password_confirmation:"",terms:!1,roles_user:"",eventos:0,candidato:0,votantes:"",parametro:"",subtipo:0}),p=_(null),u=_(!1),m=[{url:"/users",text:"Listado de usuarios"},{url:"",text:"Añadir usuario"}];U(d,b=>{b=="Candidato"?t.candidato=1:t.candidato=0});const x=()=>{var b;return t.subtipo==0?"por default":typeof t.subtipo=="object"?t.subtipo.detalle:(b=l.subtipos.find(n=>n.id===parseInt(t.subtipo)))==null?void 0:b.detalle},z=le(()=>p.value?l.subtipos.filter(b=>b.codParametro===p.value):[]);U(u,b=>{b||(t.subtipo=0)});const X=()=>{t.subtipo=typeof t.subtipo!="object"?t.subtipo:t.subtipo.id,t.post(route("users.store"),{onSuccess:function(){t.reset("password","password_confirmation"),o({title:"Registro Guardado",text:"El usuario se ha almacenado exitosamente",icon:"success"})}})},Y=b=>{const n=b.target.files[0];if(!n){t.errors.votantes="Por favor, selecciona un archivo.";return}if(n.size>2*1024*1024){t.errors.votantes="El archivo no debe ser mayor a 2 MB.";return}t.errors.votantes=null,t.votantes=n,console.log(t.votantes)},Z=()=>{t.post(route("cargueVotantes"),{forceFormData:!0,onSuccess:()=>{l.numRegistrosInsertados!==void 0&&l.numRegistrosActualizados!==void 0?o({title:"Registros Cargados",text:`Se han importado ${l.numRegistrosInsertados} nuevos registros correctamente y ${l.numRegistrosActualizados} actualizados`,icon:"success"}):l.numRegistrosInsertados!==void 0&&l.numRegistrosActualizados==null?o({title:"Registros Cargados",text:`Se han importado ${l.numRegistrosInsertados} nuevos registros correctamente y 0 actualizados`,icon:"success"}):l.numRegistrosInsertados==null&&l.numRegistrosActualizados!==void 0?o({title:"Registros Cargados",text:`Se han importado 0 nuevos registros correctamente y ${l.numRegistrosActualizados} actualizados`,icon:"success"}):o({title:"Registros Cargados",text:"Se han importado los registros de forma masiva",icon:"success"})},onError:b=>{o({title:"Error",text:"Hubo un problema al procesar la solicitud.",icon:"error"})}})};return(b,n)=>(c(),g(k,null,[i(a(se),{title:"Agregar usuario"}),i(re,{breadCrumbLinks:m},{header:w(()=>n[15]||(n[15]=[h(" Nuevo usuario ")])),default:w(()=>[s("div",Ce,[s("div",Pe,[s("div",Ie,[n[17]||(n[17]=s("h3",{class:"mt-1 text-gray-500"},"Nuevo Usuario",-1)),s("div",Te,[i(ie,{href:b.route("users.index")},{default:w(()=>n[16]||(n[16]=[h(" Regresar ")])),_:1},8,["href"])])]),s("div",je,[s("form",{onSubmit:E(X,["prevent"]),class:"grid sm:grid-cols-2 gap-4"},[s("div",null,[i(y,{for:"name",value:"Nombre"}),i(S,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:a(t).name,"onUpdate:modelValue":n[0]||(n[0]=r=>a(t).name=r),required:"",autofocus:"",autocomplete:"name"},null,8,["modelValue"]),i(v,{class:"mt-2",message:a(t).errors.name},null,8,["message"])]),s("div",null,[i(y,{for:"identificacion",value:"Identificación"}),i(S,{id:"identificacion",type:"number",class:"mt-1 block w-full",modelValue:a(t).identificacion,"onUpdate:modelValue":n[1]||(n[1]=r=>a(t).identificacion=r),autofocus:"",autocomplete:"identificacion"},null,8,["modelValue"]),i(v,{class:"mt-2",message:a(t).errors.identificacion},null,8,["message"])]),s("div",null,[i(y,{for:"tipo",value:"Tipo votante"}),e.tipos.length?O((c(),g("select",{key:0,"onUpdate:modelValue":n[2]||(n[2]=r=>a(t).tipo=r),class:"block w-full px-4 py-2 mt-1 text-base text-gray-900 border border-gray-600 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"},[n[18]||(n[18]=s("option",{disabled:"",value:"",class:"text-xs text-gray-400"}," Seleccione tipo votante ",-1)),(c(!0),g(k,null,L(e.tipos,r=>(c(),g("option",{value:r.nombre,key:r.id},$(r.nombre),9,Ue))),128))],512)),[[R,a(t).tipo]]):(c(),g("p",Ee,[n[19]||(n[19]=h(" No hay tipos de usuarios registrados, registrar uno nuevo ")),s("a",{href:b.route("tipos.index")},"Aquí",8,De)])),i(v,{class:"mt-2",message:a(t).errors.tipo},null,8,["message"])]),s("div",null,[i(y,{for:"evento",value:"Evento votante"}),e.eventos.length?O((c(),g("select",{key:0,"onUpdate:modelValue":n[3]||(n[3]=r=>a(t).eventos=r),class:"block w-full px-4 py-2 mt-1 text-base text-gray-900 border border-gray-600 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"},[n[20]||(n[20]=s("option",{disabled:"",value:"",class:"text-xs text-gray-400"}," Seleccione evento a asignar ",-1)),(c(!0),g(k,null,L(e.eventos,r=>(c(),g("option",{value:r.id,key:r.id},$(r.nombre),9,Ne))),128))],512)),[[R,a(t).eventos]]):(c(),g("p",qe,[n[21]||(n[21]=h(" No hay eventos registrados, registrar uno nuevo ")),s("a",{href:b.route("eventos.index")},"Aquí",8,Ke)])),i(v,{class:"mt-2",message:a(t).errors.eventos},null,8,["message"])]),s("div",Fe,[s("div",Me,[s("div",He,[s("div",Ge,[i(y,{for:"tipo_user",value:"Subtipos - "+x()},null,8,["value"]),i(ce,{onClick:n[4]||(n[4]=r=>u.value=!u.value),class:"items-center"},{default:w(()=>[h($(u.value?"Cancelar":"Agregar subtipo"),1)]),_:1})]),u.value?(c(),g("div",We,[s("div",null,[i(y,{for:"parametro",value:"Parámetro"}),i(a(ue),{id:"parametro",modelValue:p.value,"onUpdate:modelValue":n[5]||(n[5]=r=>p.value=r),options:e.parametros,"option-label":"detalle","option-value":"cod",placeholder:"Seleccione un parámetro",class:"w-full"},null,8,["modelValue","options"]),i(v,{class:"mt-2",message:a(t).errors.parametro},null,8,["message"])]),s("div",Xe,[i(y,{for:"parametroDetalle",value:"Subtipo"}),i(a(de),{id:"parametroDetalle",modelValue:a(t).subtipo,"onUpdate:modelValue":n[6]||(n[6]=r=>a(t).subtipo=r),options:z.value,filter:"",optionLabel:"detalle",placeholder:"Seleccione un subtipo",checkmark:"",highlightOnSelect:!1,class:"w-full"},null,8,["modelValue","options"]),i(v,{class:"mt-2",message:a(t).errors.subtipo},null,8,["message"])])])):q("",!0)])])]),s("div",null,[i(y,{for:"email",value:"Usuario"}),i(S,{id:"email",type:"text",class:"mt-1 block w-full",modelValue:a(t).email,"onUpdate:modelValue":n[7]||(n[7]=r=>a(t).email=r),required:"",autocomplete:"username"},null,8,["modelValue"]),i(v,{class:"mt-2",message:a(t).errors.email},null,8,["message"])]),s("div",null,[i(y,{for:"password",value:"Contraseña"}),i(S,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:a(t).password,"onUpdate:modelValue":n[8]||(n[8]=r=>a(t).password=r),required:"",autocomplete:"new-password"},null,8,["modelValue"]),i(v,{class:"mt-2",message:a(t).errors.password},null,8,["message"])]),s("div",null,[i(y,{for:"password_confirmation",value:"Confirmar contraseña"}),i(S,{id:"password_confirmation",type:"password",class:"mt-1 block w-full",modelValue:a(t).password_confirmation,"onUpdate:modelValue":n[9]||(n[9]=r=>a(t).password_confirmation=r),required:"",autocomplete:"new-password"},null,8,["modelValue"]),i(v,{class:"mt-2",message:a(t).errors.password_confirmation},null,8,["message"])]),s("div",null,[i(y,{for:"roles",value:"Roles"}),s("div",Ye,[i(a(ae),{id:"roles",modelValue:a(t).roles_user,"onUpdate:modelValue":n[10]||(n[10]=r=>a(t).roles_user=r),display:"chip",options:Object.values(e.roles),placeholder:"Seleccione rol",maxSelectedLabels:3,class:"w-full md:w-20rem"},null,8,["modelValue","options"])]),i(v,{class:"mt-2",message:a(t).errors.roles_user},null,8,["message"])]),s("div",Ze,[s("div",Je,[s("div",Qe,[i(a(W),{modelValue:d.value,"onUpdate:modelValue":n[11]||(n[11]=r=>d.value=r),options:f.value,"aria-labelledby":"basic"},null,8,["modelValue","options"])])])]),s("div",et,[s("div",tt,[i(D,{class:I({"opacity-25":a(t).processing}),disabled:a(t).processing},{default:w(()=>n[22]||(n[22]=[h(" Registrar Usuario ")])),_:1},8,["class","disabled"])])])],32)])]),s("div",ot,[n[31]||(n[31]=s("div",{class:"bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 flex"},[s("h3",{class:"text-gray-500 m-auto"},"CARGUE MASIVO")],-1)),s("div",nt,[s("div",lt,[n[29]||(n[29]=s("p",{class:""},[s("b",null,"Nota:"),h(" Para subir masivamente la lista de votantes, debe descargar la plantilla, dando clic en el siguiente botón. ")],-1)),s("a",{href:b.route("plantillaRes.excel"),class:"flex inline-flex text-white bg-green-800 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2 mt-4"},n[23]||(n[23]=[s("i",{class:"fa-solid fa-file-arrow-down m-auto me-2"},null,-1),h("Descargar plantilla ")]),8,st),n[30]||(n[30]=s("p",{class:"mt-4"}," Luego de descargar la plantilla y cargar los datos, subir el mismo archivo excel en el apartado de abajo. ",-1)),s("form",{onSubmit:E(Z,["prevent"])},[s("div",rt,[i(y,{for:"tipo",value:"Tipo votante"}),e.tipos.length?O((c(),g("select",{key:0,"onUpdate:modelValue":n[12]||(n[12]=r=>a(t).tipo=r),class:"block w-full px-4 py-1 mt-1 text-base text-gray-900 border border-gray-600 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"},[n[24]||(n[24]=s("option",{disabled:"",value:"",class:"text-xs text-gray-400"}," Seleccione tipo votante ",-1)),(c(!0),g(k,null,L(e.tipos,r=>(c(),g("option",{value:r.nombre,key:r.id},$(r.nombre),9,at))),128))],512)),[[R,a(t).tipo]]):(c(),g("p",it,[n[25]||(n[25]=h(" No hay tipos de usuarios registrados, registrar uno nuevo ")),s("a",{href:b.route("tipos.index")},"Aquí",8,ut)])),i(v,{class:"mt-2",message:a(t).errors.tipo},null,8,["message"])]),s("div",dt,[i(y,{for:"evento",value:"Evento votante"}),e.eventos.length?O((c(),g("select",{key:0,"onUpdate:modelValue":n[13]||(n[13]=r=>a(t).eventos=r),class:"block w-full px-4 py-1 mt-1 text-base text-gray-900 border border-gray-600 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"},[n[26]||(n[26]=s("option",{disabled:"",value:"",class:"text-xs text-gray-400"}," Seleccione evento a asignar ",-1)),(c(!0),g(k,null,L(e.eventos,r=>(c(),g("option",{value:r.id,key:r.id},$(r.nombre),9,ct))),128))],512)),[[R,a(t).eventos]]):(c(),g("p",gt,[n[27]||(n[27]=h(" No hay eventos registrados, registrar uno nuevo ")),s("a",{href:b.route("eventos.index")},"Aquí",8,pt)])),i(v,{class:"mt-2",message:a(t).errors.eventos},null,8,["message"])]),i(S,{id:"cargueMasivoVotantes",type:"file",class:"mt-4 block w-full",onChange:n[14]||(n[14]=r=>Y(r))}),i(v,{class:"mt-2",message:a(t).errors.votantes},null,8,["message"]),s("div",null,[s("div",bt,[i(D,{class:I({"opacity-25":a(t).processing}),disabled:a(t).processing},{default:w(()=>n[28]||(n[28]=[h(" Enviar ")])),_:1},8,["class","disabled"])])])],32)])])])])]),_:1})],64))}};export{Ut as default};
