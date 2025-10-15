import{c as d,o as r,e as b,K as o,J as ee,S as L,U as te,V as ne,W as P,X as ie,Y as se,a0 as z,a1 as g,a2 as le,a3 as oe,a4 as re,a5 as ae,a6 as de,a7 as $,a8 as ce,a9 as ue,aa as pe,ab as fe,ac as he,ad as m,A as y,a as be,G as f,p as O,b as I,j as D,t as v,l as k,H,n as x,d as S,P as ye,ae as ge,af as ve,F as A,k as me,f as Oe}from"./app-Ci4tAQdo.js";import{s as Ie,R as ke}from"./index-BV9jdx8-.js";import{s as Se,a as we,b as Le,c as Fe,d as Ce,e as Ve,f as Me,g as xe,O as Ke}from"./index-CYjbKLRT.js";import{s as Te}from"./index-CYPV4c1x.js";import{s as Ee}from"./index-BN1yKdZO.js";import{a as $e}from"./index-B9sxCayH.js";var j={name:"BlankIcon",extends:Ie};function De(e,t,n,s,l,i){return r(),d("svg",o({width:"14",height:"14",viewBox:"0 0 14 14",fill:"none",xmlns:"http://www.w3.org/2000/svg"},e.pti()),t[0]||(t[0]=[b("rect",{width:"1",height:"1",fill:"currentColor","fill-opacity":"0"},null,-1)]),16)}j.render=De;var Ae=({dt:e})=>`
.p-select {
    display: inline-flex;
    cursor: pointer;
    position: relative;
    user-select: none;
    background: ${e("select.background")};
    border: 1px solid ${e("select.border.color")};
    transition: background ${e("select.transition.duration")}, color ${e("select.transition.duration")}, border-color ${e("select.transition.duration")},
        outline-color ${e("select.transition.duration")}, box-shadow ${e("select.transition.duration")};
    border-radius: ${e("select.border.radius")};
    outline-color: transparent;
    box-shadow: ${e("select.shadow")};
}

.p-select:not(.p-disabled):hover {
    border-color: ${e("select.hover.border.color")};
}

.p-select:not(.p-disabled).p-focus {
    border-color: ${e("select.focus.border.color")};
    box-shadow: ${e("select.focus.ring.shadow")};
    outline: ${e("select.focus.ring.width")} ${e("select.focus.ring.style")} ${e("select.focus.ring.color")};
    outline-offset: ${e("select.focus.ring.offset")};
}

.p-select.p-variant-filled {
    background: ${e("select.filled.background")};
}

.p-select.p-variant-filled:not(.p-disabled):hover {
    background: ${e("select.filled.hover.background")};
}

.p-select.p-variant-filled:not(.p-disabled).p-focus {
    background: ${e("select.filled.focus.background")};
}

.p-select.p-invalid {
    border-color: ${e("select.invalid.border.color")};
}

.p-select.p-disabled {
    opacity: 1;
    background: ${e("select.disabled.background")};
}

.p-select-clear-icon {
    position: absolute;
    top: 50%;
    margin-top: -0.5rem;
    color: ${e("select.clear.icon.color")};
    inset-inline-end: ${e("select.dropdown.width")};
}

.p-select-dropdown {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: transparent;
    color: ${e("select.dropdown.color")};
    width: ${e("select.dropdown.width")};
    border-start-end-radius: ${e("select.border.radius")};
    border-end-end-radius: ${e("select.border.radius")};
}

.p-select-label {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    flex: 1 1 auto;
    width: 1%;
    padding: ${e("select.padding.y")} ${e("select.padding.x")};
    text-overflow: ellipsis;
    cursor: pointer;
    color: ${e("select.color")};
    background: transparent;
    border: 0 none;
    outline: 0 none;
}

.p-select-label.p-placeholder {
    color: ${e("select.placeholder.color")};
}

.p-select.p-invalid .p-select-label.p-placeholder {
    color: ${e("select.invalid.placeholder.color")};
}

.p-select:has(.p-select-clear-icon) .p-select-label {
    padding-inline-end: calc(1rem + ${e("select.padding.x")});
}

.p-select.p-disabled .p-select-label {
    color: ${e("select.disabled.color")};
}

.p-select-label-empty {
    overflow: hidden;
    opacity: 0;
}

input.p-select-label {
    cursor: default;
}

.p-select .p-select-overlay {
    min-width: 100%;
}

.p-select-overlay {
    position: absolute;
    top: 0;
    left: 0;
    background: ${e("select.overlay.background")};
    color: ${e("select.overlay.color")};
    border: 1px solid ${e("select.overlay.border.color")};
    border-radius: ${e("select.overlay.border.radius")};
    box-shadow: ${e("select.overlay.shadow")};
}

.p-select-header {
    padding: ${e("select.list.header.padding")};
}

.p-select-filter {
    width: 100%;
}

.p-select-list-container {
    overflow: auto;
}

.p-select-option-group {
    cursor: auto;
    margin: 0;
    padding: ${e("select.option.group.padding")};
    background: ${e("select.option.group.background")};
    color: ${e("select.option.group.color")};
    font-weight: ${e("select.option.group.font.weight")};
}

.p-select-list {
    margin: 0;
    padding: 0;
    list-style-type: none;
    padding: ${e("select.list.padding")};
    gap: ${e("select.list.gap")};
    display: flex;
    flex-direction: column;
}

.p-select-option {
    cursor: pointer;
    font-weight: normal;
    white-space: nowrap;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: ${e("select.option.padding")};
    border: 0 none;
    color: ${e("select.option.color")};
    background: transparent;
    transition: background ${e("select.transition.duration")}, color ${e("select.transition.duration")}, border-color ${e("select.transition.duration")},
            box-shadow ${e("select.transition.duration")}, outline-color ${e("select.transition.duration")};
    border-radius: ${e("select.option.border.radius")};
}

.p-select-option:not(.p-select-option-selected):not(.p-disabled).p-focus {
    background: ${e("select.option.focus.background")};
    color: ${e("select.option.focus.color")};
}

.p-select-option.p-select-option-selected {
    background: ${e("select.option.selected.background")};
    color: ${e("select.option.selected.color")};
}

.p-select-option.p-select-option-selected.p-focus {
    background: ${e("select.option.selected.focus.background")};
    color: ${e("select.option.selected.focus.color")};
}

.p-select-option-blank-icon {
    flex-shrink: 0;
}

.p-select-option-check-icon {
    position: relative;
    flex-shrink: 0;
    margin-inline-start: ${e("select.checkmark.gutter.start")};
    margin-inline-end: ${e("select.checkmark.gutter.end")};
    color: ${e("select.checkmark.color")};
}

.p-select-empty-message {
    padding: ${e("select.empty.message.padding")};
}

.p-select-fluid {
    display: flex;
    width: 100%;
}

.p-select-sm .p-select-label {
    font-size: ${e("select.sm.font.size")};
    padding-block: ${e("select.sm.padding.y")};
    padding-inline: ${e("select.sm.padding.x")};
}

.p-select-sm .p-select-dropdown .p-icon {
    font-size: ${e("select.sm.font.size")};
    width: ${e("select.sm.font.size")};
    height: ${e("select.sm.font.size")};
}

.p-select-lg .p-select-label {
    font-size: ${e("select.lg.font.size")};
    padding-block: ${e("select.lg.padding.y")};
    padding-inline: ${e("select.lg.padding.x")};
}

.p-select-lg .p-select-dropdown .p-icon {
    font-size: ${e("select.lg.font.size")};
    width: ${e("select.lg.font.size")};
    height: ${e("select.lg.font.size")};
}
`,Be={root:function(t){var n=t.instance,s=t.props,l=t.state;return["p-select p-component p-inputwrapper",{"p-disabled":s.disabled,"p-invalid":n.$invalid,"p-variant-filled":n.$variant==="filled","p-focus":l.focused,"p-inputwrapper-filled":n.$filled,"p-inputwrapper-focus":l.focused||l.overlayVisible,"p-select-open":l.overlayVisible,"p-select-fluid":n.$fluid,"p-select-sm p-inputfield-sm":s.size==="small","p-select-lg p-inputfield-lg":s.size==="large"}]},label:function(t){var n=t.instance,s=t.props;return["p-select-label",{"p-placeholder":!s.editable&&n.label===s.placeholder,"p-select-label-empty":!s.editable&&!n.$slots.value&&(n.label==="p-emptylabel"||n.label.length===0)}]},clearIcon:"p-select-clear-icon",dropdown:"p-select-dropdown",loadingicon:"p-select-loading-icon",dropdownIcon:"p-select-dropdown-icon",overlay:"p-select-overlay p-component",header:"p-select-header",pcFilter:"p-select-filter",listContainer:"p-select-list-container",list:"p-select-list",optionGroup:"p-select-option-group",optionGroupLabel:"p-select-option-group-label",option:function(t){var n=t.instance,s=t.props,l=t.state,i=t.option,c=t.focusedOption;return["p-select-option",{"p-select-option-selected":n.isSelected(i)&&s.highlightOnSelect,"p-focus":l.focusedOptionIndex===c,"p-disabled":n.isOptionDisabled(i)}]},optionLabel:"p-select-option-label",optionCheckIcon:"p-select-option-check-icon",optionBlankIcon:"p-select-option-blank-icon",emptyMessage:"p-select-empty-message"},Pe=ee.extend({name:"select",style:Ae,classes:Be}),ze={name:"BaseSelect",extends:$e,props:{options:Array,optionLabel:[String,Function],optionValue:[String,Function],optionDisabled:[String,Function],optionGroupLabel:[String,Function],optionGroupChildren:[String,Function],scrollHeight:{type:String,default:"14rem"},filter:Boolean,filterPlaceholder:String,filterLocale:String,filterMatchMode:{type:String,default:"contains"},filterFields:{type:Array,default:null},editable:Boolean,placeholder:{type:String,default:null},dataKey:null,showClear:{type:Boolean,default:!1},inputId:{type:String,default:null},inputClass:{type:[String,Object],default:null},inputStyle:{type:Object,default:null},labelId:{type:String,default:null},labelClass:{type:[String,Object],default:null},labelStyle:{type:Object,default:null},panelClass:{type:[String,Object],default:null},overlayStyle:{type:Object,default:null},overlayClass:{type:[String,Object],default:null},panelStyle:{type:Object,default:null},appendTo:{type:[String,Object],default:"body"},loading:{type:Boolean,default:!1},clearIcon:{type:String,default:void 0},dropdownIcon:{type:String,default:void 0},filterIcon:{type:String,default:void 0},loadingIcon:{type:String,default:void 0},resetFilterOnHide:{type:Boolean,default:!1},resetFilterOnClear:{type:Boolean,default:!1},virtualScrollerOptions:{type:Object,default:null},autoOptionFocus:{type:Boolean,default:!1},autoFilterFocus:{type:Boolean,default:!1},selectOnFocus:{type:Boolean,default:!1},focusOnHover:{type:Boolean,default:!0},highlightOnSelect:{type:Boolean,default:!0},checkmark:{type:Boolean,default:!1},filterMessage:{type:String,default:null},selectionMessage:{type:String,default:null},emptySelectionMessage:{type:String,default:null},emptyFilterMessage:{type:String,default:null},emptyMessage:{type:String,default:null},tabindex:{type:Number,default:0},ariaLabel:{type:String,default:null},ariaLabelledby:{type:String,default:null}},style:Pe,provide:function(){return{$pcSelect:this,$parentInstance:this}}};function F(e){"@babel/helpers - typeof";return F=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(t){return typeof t}:function(t){return t&&typeof Symbol=="function"&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},F(e)}function He(e){return Ue(e)||je(e)||Re(e)||Ge()}function Ge(){throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Re(e,t){if(e){if(typeof e=="string")return B(e,t);var n={}.toString.call(e).slice(8,-1);return n==="Object"&&e.constructor&&(n=e.constructor.name),n==="Map"||n==="Set"?Array.from(e):n==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?B(e,t):void 0}}function je(e){if(typeof Symbol<"u"&&e[Symbol.iterator]!=null||e["@@iterator"]!=null)return Array.from(e)}function Ue(e){if(Array.isArray(e))return B(e)}function B(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,s=Array(t);n<t;n++)s[n]=e[n];return s}function G(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var s=Object.getOwnPropertySymbols(e);t&&(s=s.filter(function(l){return Object.getOwnPropertyDescriptor(e,l).enumerable})),n.push.apply(n,s)}return n}function R(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]!=null?arguments[t]:{};t%2?G(Object(n),!0).forEach(function(s){U(e,s,n[s])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):G(Object(n)).forEach(function(s){Object.defineProperty(e,s,Object.getOwnPropertyDescriptor(n,s))})}return e}function U(e,t,n){return(t=Ne(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Ne(e){var t=qe(e,"string");return F(t)=="symbol"?t:t+""}function qe(e,t){if(F(e)!="object"||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var s=n.call(e,t);if(F(s)!="object")return s;throw new TypeError("@@toPrimitive must return a primitive value.")}return(t==="string"?String:Number)(e)}var We={name:"Select",extends:ze,inheritAttrs:!1,emits:["change","focus","blur","before-show","before-hide","show","hide","filter"],outsideClickListener:null,scrollHandler:null,resizeListener:null,labelClickListener:null,matchMediaOrientationListener:null,overlay:null,list:null,virtualScroller:null,searchTimeout:null,searchValue:null,isModelValueChanged:!1,data:function(){return{clicked:!1,focused:!1,focusedOptionIndex:-1,filterValue:null,overlayVisible:!1,queryOrientation:null}},watch:{modelValue:function(){this.isModelValueChanged=!0},options:function(){this.autoUpdateModel()}},mounted:function(){this.autoUpdateModel(),this.bindLabelClickListener(),this.bindMatchMediaOrientationListener()},updated:function(){this.overlayVisible&&this.isModelValueChanged&&this.scrollInView(this.findSelectedOptionIndex()),this.isModelValueChanged=!1},beforeUnmount:function(){this.unbindOutsideClickListener(),this.unbindResizeListener(),this.unbindLabelClickListener(),this.unbindMatchMediaOrientationListener(),this.scrollHandler&&(this.scrollHandler.destroy(),this.scrollHandler=null),this.overlay&&($.clear(this.overlay),this.overlay=null)},methods:{getOptionIndex:function(t,n){return this.virtualScrollerDisabled?t:n&&n(t).index},getOptionLabel:function(t){return this.optionLabel?m(t,this.optionLabel):t},getOptionValue:function(t){return this.optionValue?m(t,this.optionValue):t},getOptionRenderKey:function(t,n){return(this.dataKey?m(t,this.dataKey):this.getOptionLabel(t))+"_"+n},getPTItemOptions:function(t,n,s,l){return this.ptm(l,{context:{option:t,index:s,selected:this.isSelected(t),focused:this.focusedOptionIndex===this.getOptionIndex(s,n),disabled:this.isOptionDisabled(t)}})},isOptionDisabled:function(t){return this.optionDisabled?m(t,this.optionDisabled):!1},isOptionGroup:function(t){return this.optionGroupLabel&&t.optionGroup&&t.group},getOptionGroupLabel:function(t){return m(t,this.optionGroupLabel)},getOptionGroupChildren:function(t){return m(t,this.optionGroupChildren)},getAriaPosInset:function(t){var n=this;return(this.optionGroupLabel?t-this.visibleOptions.slice(0,t).filter(function(s){return n.isOptionGroup(s)}).length:t)+1},show:function(t){this.$emit("before-show"),this.overlayVisible=!0,this.focusedOptionIndex=this.focusedOptionIndex!==-1?this.focusedOptionIndex:this.autoOptionFocus?this.findFirstFocusedOptionIndex():this.editable?-1:this.findSelectedOptionIndex(),t&&g(this.$refs.focusInput)},hide:function(t){var n=this,s=function(){n.$emit("before-hide"),n.overlayVisible=!1,n.clicked=!1,n.focusedOptionIndex=-1,n.searchValue="",n.resetFilterOnHide&&(n.filterValue=null),t&&g(n.$refs.focusInput)};setTimeout(function(){s()},0)},onFocus:function(t){this.disabled||(this.focused=!0,this.overlayVisible&&(this.focusedOptionIndex=this.focusedOptionIndex!==-1?this.focusedOptionIndex:this.autoOptionFocus?this.findFirstFocusedOptionIndex():this.editable?-1:this.findSelectedOptionIndex(),this.scrollInView(this.focusedOptionIndex)),this.$emit("focus",t))},onBlur:function(t){var n,s;this.focused=!1,this.focusedOptionIndex=-1,this.searchValue="",this.$emit("blur",t),(n=(s=this.formField).onBlur)===null||n===void 0||n.call(s,t)},onKeyDown:function(t){if(this.disabled||fe()){t.preventDefault();return}var n=t.metaKey||t.ctrlKey;switch(t.code){case"ArrowDown":this.onArrowDownKey(t);break;case"ArrowUp":this.onArrowUpKey(t,this.editable);break;case"ArrowLeft":case"ArrowRight":this.onArrowLeftKey(t,this.editable);break;case"Home":this.onHomeKey(t,this.editable);break;case"End":this.onEndKey(t,this.editable);break;case"PageDown":this.onPageDownKey(t);break;case"PageUp":this.onPageUpKey(t);break;case"Space":this.onSpaceKey(t,this.editable);break;case"Enter":case"NumpadEnter":this.onEnterKey(t);break;case"Escape":this.onEscapeKey(t);break;case"Tab":this.onTabKey(t);break;case"Backspace":this.onBackspaceKey(t,this.editable);break;case"ShiftLeft":case"ShiftRight":break;default:!n&&he(t.key)&&(!this.overlayVisible&&this.show(),!this.editable&&this.searchOptions(t,t.key));break}this.clicked=!1},onEditableInput:function(t){var n=t.target.value;this.searchValue="";var s=this.searchOptions(t,n);!s&&(this.focusedOptionIndex=-1),this.updateModel(t,n),!this.overlayVisible&&L(n)&&this.show()},onContainerClick:function(t){this.disabled||this.loading||t.target.tagName==="INPUT"||t.target.getAttribute("data-pc-section")==="clearicon"||t.target.closest('[data-pc-section="clearicon"]')||((!this.overlay||!this.overlay.contains(t.target))&&(this.overlayVisible?this.hide(!0):this.show(!0)),this.clicked=!0)},onClearClick:function(t){this.updateModel(t,null),this.resetFilterOnClear&&(this.filterValue=null)},onFirstHiddenFocus:function(t){var n=t.relatedTarget===this.$refs.focusInput?pe(this.overlay,':not([data-p-hidden-focusable="true"])'):this.$refs.focusInput;g(n)},onLastHiddenFocus:function(t){var n=t.relatedTarget===this.$refs.focusInput?ue(this.overlay,':not([data-p-hidden-focusable="true"])'):this.$refs.focusInput;g(n)},onOptionSelect:function(t,n){var s=arguments.length>2&&arguments[2]!==void 0?arguments[2]:!0,l=this.getOptionValue(n);this.updateModel(t,l),s&&this.hide(!0)},onOptionMouseMove:function(t,n){this.focusOnHover&&this.changeFocusedOptionIndex(t,n)},onFilterChange:function(t){var n=t.target.value;this.filterValue=n,this.focusedOptionIndex=-1,this.$emit("filter",{originalEvent:t,value:n}),!this.virtualScrollerDisabled&&this.virtualScroller.scrollToIndex(0)},onFilterKeyDown:function(t){if(!t.isComposing)switch(t.code){case"ArrowDown":this.onArrowDownKey(t);break;case"ArrowUp":this.onArrowUpKey(t,!0);break;case"ArrowLeft":case"ArrowRight":this.onArrowLeftKey(t,!0);break;case"Home":this.onHomeKey(t,!0);break;case"End":this.onEndKey(t,!0);break;case"Enter":case"NumpadEnter":this.onEnterKey(t);break;case"Escape":this.onEscapeKey(t);break;case"Tab":this.onTabKey(t,!0);break}},onFilterBlur:function(){this.focusedOptionIndex=-1},onFilterUpdated:function(){this.overlayVisible&&this.alignOverlay()},onOverlayClick:function(t){Ke.emit("overlay-click",{originalEvent:t,target:this.$el})},onOverlayKeyDown:function(t){switch(t.code){case"Escape":this.onEscapeKey(t);break}},onArrowDownKey:function(t){if(!this.overlayVisible)this.show(),this.editable&&this.changeFocusedOptionIndex(t,this.findSelectedOptionIndex());else{var n=this.focusedOptionIndex!==-1?this.findNextOptionIndex(this.focusedOptionIndex):this.clicked?this.findFirstOptionIndex():this.findFirstFocusedOptionIndex();this.changeFocusedOptionIndex(t,n)}t.preventDefault()},onArrowUpKey:function(t){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1;if(t.altKey&&!n)this.focusedOptionIndex!==-1&&this.onOptionSelect(t,this.visibleOptions[this.focusedOptionIndex]),this.overlayVisible&&this.hide(),t.preventDefault();else{var s=this.focusedOptionIndex!==-1?this.findPrevOptionIndex(this.focusedOptionIndex):this.clicked?this.findLastOptionIndex():this.findLastFocusedOptionIndex();this.changeFocusedOptionIndex(t,s),!this.overlayVisible&&this.show(),t.preventDefault()}},onArrowLeftKey:function(t){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1;n&&(this.focusedOptionIndex=-1)},onHomeKey:function(t){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1;if(n){var s=t.currentTarget;t.shiftKey?s.setSelectionRange(0,t.target.selectionStart):(s.setSelectionRange(0,0),this.focusedOptionIndex=-1)}else this.changeFocusedOptionIndex(t,this.findFirstOptionIndex()),!this.overlayVisible&&this.show();t.preventDefault()},onEndKey:function(t){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1;if(n){var s=t.currentTarget;if(t.shiftKey)s.setSelectionRange(t.target.selectionStart,s.value.length);else{var l=s.value.length;s.setSelectionRange(l,l),this.focusedOptionIndex=-1}}else this.changeFocusedOptionIndex(t,this.findLastOptionIndex()),!this.overlayVisible&&this.show();t.preventDefault()},onPageUpKey:function(t){this.scrollInView(0),t.preventDefault()},onPageDownKey:function(t){this.scrollInView(this.visibleOptions.length-1),t.preventDefault()},onEnterKey:function(t){this.overlayVisible?(this.focusedOptionIndex!==-1&&this.onOptionSelect(t,this.visibleOptions[this.focusedOptionIndex]),this.hide()):(this.focusedOptionIndex=-1,this.onArrowDownKey(t)),t.preventDefault()},onSpaceKey:function(t){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1;!n&&this.onEnterKey(t)},onEscapeKey:function(t){this.overlayVisible&&this.hide(!0),t.preventDefault(),t.stopPropagation()},onTabKey:function(t){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1;n||(this.overlayVisible&&this.hasFocusableElements()?(g(this.$refs.firstHiddenFocusableElementOnOverlay),t.preventDefault()):(this.focusedOptionIndex!==-1&&this.onOptionSelect(t,this.visibleOptions[this.focusedOptionIndex]),this.overlayVisible&&this.hide(this.filter)))},onBackspaceKey:function(t){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1;n&&!this.overlayVisible&&this.show()},onOverlayEnter:function(t){var n=this;$.set("overlay",t,this.$primevue.config.zIndex.overlay),ce(t,{position:"absolute",top:"0",left:"0"}),this.alignOverlay(),this.scrollInView(),setTimeout(function(){n.autoFilterFocus&&n.filter&&g(n.$refs.filterInput.$el),n.autoUpdateModel()},1)},onOverlayAfterEnter:function(){this.bindOutsideClickListener(),this.bindScrollListener(),this.bindResizeListener(),this.$emit("show")},onOverlayLeave:function(){var t=this;this.unbindOutsideClickListener(),this.unbindScrollListener(),this.unbindResizeListener(),this.autoFilterFocus&&this.filter&&!this.editable&&this.$nextTick(function(){t.$refs.filterInput&&g(t.$refs.filterInput.$el)}),this.$emit("hide"),this.overlay=null},onOverlayAfterLeave:function(t){$.clear(t)},alignOverlay:function(){this.appendTo==="self"?re(this.overlay,this.$el):this.overlay&&(this.overlay.style.minWidth=ae(this.$el)+"px",de(this.overlay,this.$el))},bindOutsideClickListener:function(){var t=this;this.outsideClickListener||(this.outsideClickListener=function(n){t.overlayVisible&&t.overlay&&!t.$el.contains(n.target)&&!t.overlay.contains(n.target)&&t.hide()},document.addEventListener("click",this.outsideClickListener,!0))},unbindOutsideClickListener:function(){this.outsideClickListener&&(document.removeEventListener("click",this.outsideClickListener,!0),this.outsideClickListener=null)},bindScrollListener:function(){var t=this;this.scrollHandler||(this.scrollHandler=new oe(this.$refs.container,function(){t.overlayVisible&&t.hide()})),this.scrollHandler.bindScrollListener()},unbindScrollListener:function(){this.scrollHandler&&this.scrollHandler.unbindScrollListener()},bindResizeListener:function(){var t=this;this.resizeListener||(this.resizeListener=function(){t.overlayVisible&&!le()&&t.hide()},window.addEventListener("resize",this.resizeListener))},unbindResizeListener:function(){this.resizeListener&&(window.removeEventListener("resize",this.resizeListener),this.resizeListener=null)},bindLabelClickListener:function(){var t=this;if(!this.editable&&!this.labelClickListener){var n=document.querySelector('label[for="'.concat(this.labelId,'"]'));n&&z(n)&&(this.labelClickListener=function(){g(t.$refs.focusInput)},n.addEventListener("click",this.labelClickListener))}},unbindLabelClickListener:function(){if(this.labelClickListener){var t=document.querySelector('label[for="'.concat(this.labelId,'"]'));t&&z(t)&&t.removeEventListener("click",this.labelClickListener)}},bindMatchMediaOrientationListener:function(){var t=this;if(!this.matchMediaOrientationListener){var n=matchMedia("(orientation: portrait)");this.queryOrientation=n,this.matchMediaOrientationListener=function(){t.alignOverlay()},this.queryOrientation.addEventListener("change",this.matchMediaOrientationListener)}},unbindMatchMediaOrientationListener:function(){this.matchMediaOrientationListener&&(this.queryOrientation.removeEventListener("change",this.matchMediaOrientationListener),this.queryOrientation=null,this.matchMediaOrientationListener=null)},hasFocusableElements:function(){return se(this.overlay,':not([data-p-hidden-focusable="true"])').length>0},isOptionExactMatched:function(t){var n;return this.isValidOption(t)&&typeof this.getOptionLabel(t)=="string"&&((n=this.getOptionLabel(t))===null||n===void 0?void 0:n.toLocaleLowerCase(this.filterLocale))==this.searchValue.toLocaleLowerCase(this.filterLocale)},isOptionStartsWith:function(t){var n;return this.isValidOption(t)&&typeof this.getOptionLabel(t)=="string"&&((n=this.getOptionLabel(t))===null||n===void 0?void 0:n.toLocaleLowerCase(this.filterLocale).startsWith(this.searchValue.toLocaleLowerCase(this.filterLocale)))},isValidOption:function(t){return L(t)&&!(this.isOptionDisabled(t)||this.isOptionGroup(t))},isValidSelectedOption:function(t){return this.isValidOption(t)&&this.isSelected(t)},isSelected:function(t){return ie(this.d_value,this.getOptionValue(t),this.equalityKey)},findFirstOptionIndex:function(){var t=this;return this.visibleOptions.findIndex(function(n){return t.isValidOption(n)})},findLastOptionIndex:function(){var t=this;return P(this.visibleOptions,function(n){return t.isValidOption(n)})},findNextOptionIndex:function(t){var n=this,s=t<this.visibleOptions.length-1?this.visibleOptions.slice(t+1).findIndex(function(l){return n.isValidOption(l)}):-1;return s>-1?s+t+1:t},findPrevOptionIndex:function(t){var n=this,s=t>0?P(this.visibleOptions.slice(0,t),function(l){return n.isValidOption(l)}):-1;return s>-1?s:t},findSelectedOptionIndex:function(){var t=this;return this.$filled?this.visibleOptions.findIndex(function(n){return t.isValidSelectedOption(n)}):-1},findFirstFocusedOptionIndex:function(){var t=this.findSelectedOptionIndex();return t<0?this.findFirstOptionIndex():t},findLastFocusedOptionIndex:function(){var t=this.findSelectedOptionIndex();return t<0?this.findLastOptionIndex():t},searchOptions:function(t,n){var s=this;this.searchValue=(this.searchValue||"")+n;var l=-1,i=!1;return L(this.searchValue)&&(l=this.visibleOptions.findIndex(function(c){return s.isOptionExactMatched(c)}),l===-1&&(l=this.visibleOptions.findIndex(function(c){return s.isOptionStartsWith(c)})),l!==-1&&(i=!0),l===-1&&this.focusedOptionIndex===-1&&(l=this.findFirstFocusedOptionIndex()),l!==-1&&this.changeFocusedOptionIndex(t,l)),this.searchTimeout&&clearTimeout(this.searchTimeout),this.searchTimeout=setTimeout(function(){s.searchValue="",s.searchTimeout=null},500),i},changeFocusedOptionIndex:function(t,n){this.focusedOptionIndex!==n&&(this.focusedOptionIndex=n,this.scrollInView(),this.selectOnFocus&&this.onOptionSelect(t,this.visibleOptions[n],!1))},scrollInView:function(){var t=this,n=arguments.length>0&&arguments[0]!==void 0?arguments[0]:-1;this.$nextTick(function(){var s=n!==-1?"".concat(t.$id,"_").concat(n):t.focusedOptionId,l=ne(t.list,'li[id="'.concat(s,'"]'));l?l.scrollIntoView&&l.scrollIntoView({block:"nearest",inline:"nearest"}):t.virtualScrollerDisabled||t.virtualScroller&&t.virtualScroller.scrollToIndex(n!==-1?n:t.focusedOptionIndex)})},autoUpdateModel:function(){this.autoOptionFocus&&(this.focusedOptionIndex=this.findFirstFocusedOptionIndex()),this.selectOnFocus&&this.autoOptionFocus&&!this.$filled&&this.onOptionSelect(null,this.visibleOptions[this.focusedOptionIndex],!1)},updateModel:function(t,n){this.writeValue(n,t),this.$emit("change",{originalEvent:t,value:n})},flatOptions:function(t){var n=this;return(t||[]).reduce(function(s,l,i){s.push({optionGroup:l,group:!0,index:i});var c=n.getOptionGroupChildren(l);return c&&c.forEach(function(w){return s.push(w)}),s},[])},overlayRef:function(t){this.overlay=t},listRef:function(t,n){this.list=t,n&&n(t)},virtualScrollerRef:function(t){this.virtualScroller=t}},computed:{visibleOptions:function(){var t=this,n=this.optionGroupLabel?this.flatOptions(this.options):this.options||[];if(this.filterValue){var s=te.filter(n,this.searchFields,this.filterValue,this.filterMatchMode,this.filterLocale);if(this.optionGroupLabel){var l=this.options||[],i=[];return l.forEach(function(c){var w=t.getOptionGroupChildren(c),C=w.filter(function(K){return s.includes(K)});C.length>0&&i.push(R(R({},c),{},U({},typeof t.optionGroupChildren=="string"?t.optionGroupChildren:"items",He(C))))}),this.flatOptions(i)}return s}return n},hasSelectedOption:function(){return this.$filled},label:function(){var t=this.findSelectedOptionIndex();return t!==-1?this.getOptionLabel(this.visibleOptions[t]):this.placeholder||"p-emptylabel"},editableInputValue:function(){var t=this.findSelectedOptionIndex();return t!==-1?this.getOptionLabel(this.visibleOptions[t]):this.d_value||""},equalityKey:function(){return this.optionValue?null:this.dataKey},searchFields:function(){return this.filterFields||[this.optionLabel]},filterResultMessageText:function(){return L(this.visibleOptions)?this.filterMessageText.replaceAll("{0}",this.visibleOptions.length):this.emptyFilterMessageText},filterMessageText:function(){return this.filterMessage||this.$primevue.config.locale.searchMessage||""},emptyFilterMessageText:function(){return this.emptyFilterMessage||this.$primevue.config.locale.emptySearchMessage||this.$primevue.config.locale.emptyFilterMessage||""},emptyMessageText:function(){return this.emptyMessage||this.$primevue.config.locale.emptyMessage||""},selectionMessageText:function(){return this.selectionMessage||this.$primevue.config.locale.selectionMessage||""},emptySelectionMessageText:function(){return this.emptySelectionMessage||this.$primevue.config.locale.emptySelectionMessage||""},selectedMessageText:function(){return this.$filled?this.selectionMessageText.replaceAll("{0}","1"):this.emptySelectionMessageText},focusedOptionId:function(){return this.focusedOptionIndex!==-1?"".concat(this.$id,"_").concat(this.focusedOptionIndex):null},ariaSetSize:function(){var t=this;return this.visibleOptions.filter(function(n){return!t.isOptionGroup(n)}).length},isClearIconVisible:function(){return this.showClear&&this.d_value!=null&&L(this.options)},virtualScrollerDisabled:function(){return!this.virtualScrollerOptions}},directives:{ripple:ke},components:{InputText:xe,VirtualScroller:Me,Portal:Ve,InputIcon:Ce,IconField:Fe,TimesIcon:Ee,ChevronDownIcon:Le,SpinnerIcon:Te,SearchIcon:we,CheckIcon:Se,BlankIcon:j}},Je=["id"],Xe=["name","id","value","placeholder","tabindex","disabled","aria-label","aria-labelledby","aria-expanded","aria-controls","aria-activedescendant","aria-invalid"],Ye=["name","id","tabindex","aria-label","aria-labelledby","aria-expanded","aria-controls","aria-activedescendant","aria-invalid","aria-disabled"],Ze=["id"],Qe=["id"],_e=["id","aria-label","aria-selected","aria-disabled","aria-setsize","aria-posinset","onClick","onMousemove","data-p-selected","data-p-focused","data-p-disabled"];function et(e,t,n,s,l,i){var c=y("SpinnerIcon"),w=y("InputText"),C=y("SearchIcon"),K=y("InputIcon"),N=y("IconField"),q=y("CheckIcon"),W=y("BlankIcon"),J=y("VirtualScroller"),X=y("Portal"),Y=be("ripple");return r(),d("div",o({ref:"container",id:e.$id,class:e.cx("root"),onClick:t[11]||(t[11]=function(){return i.onContainerClick&&i.onContainerClick.apply(i,arguments)})},e.ptmi("root")),[e.editable?(r(),d("input",o({key:0,ref:"focusInput",name:e.name,id:e.labelId||e.inputId,type:"text",class:[e.cx("label"),e.inputClass,e.labelClass],style:[e.inputStyle,e.labelStyle],value:i.editableInputValue,placeholder:e.placeholder,tabindex:e.disabled?-1:e.tabindex,disabled:e.disabled,autocomplete:"off",role:"combobox","aria-label":e.ariaLabel,"aria-labelledby":e.ariaLabelledby,"aria-haspopup":"listbox","aria-expanded":l.overlayVisible,"aria-controls":e.$id+"_list","aria-activedescendant":l.focused?i.focusedOptionId:void 0,"aria-invalid":e.invalid||void 0,onFocus:t[0]||(t[0]=function(){return i.onFocus&&i.onFocus.apply(i,arguments)}),onBlur:t[1]||(t[1]=function(){return i.onBlur&&i.onBlur.apply(i,arguments)}),onKeydown:t[2]||(t[2]=function(){return i.onKeyDown&&i.onKeyDown.apply(i,arguments)}),onInput:t[3]||(t[3]=function(){return i.onEditableInput&&i.onEditableInput.apply(i,arguments)})},e.ptm("label")),null,16,Xe)):(r(),d("span",o({key:1,ref:"focusInput",name:e.name,id:e.labelId||e.inputId,class:[e.cx("label"),e.inputClass,e.labelClass],style:[e.inputStyle,e.labelStyle],tabindex:e.disabled?-1:e.tabindex,role:"combobox","aria-label":e.ariaLabel||(i.label==="p-emptylabel"?void 0:i.label),"aria-labelledby":e.ariaLabelledby,"aria-haspopup":"listbox","aria-expanded":l.overlayVisible,"aria-controls":e.$id+"_list","aria-activedescendant":l.focused?i.focusedOptionId:void 0,"aria-invalid":e.invalid||void 0,"aria-disabled":e.disabled,onFocus:t[4]||(t[4]=function(){return i.onFocus&&i.onFocus.apply(i,arguments)}),onBlur:t[5]||(t[5]=function(){return i.onBlur&&i.onBlur.apply(i,arguments)}),onKeydown:t[6]||(t[6]=function(){return i.onKeyDown&&i.onKeyDown.apply(i,arguments)})},e.ptm("label")),[f(e.$slots,"value",{value:e.d_value,placeholder:e.placeholder},function(){var u;return[D(v(i.label==="p-emptylabel"?" ":(u=i.label)!==null&&u!==void 0?u:"empty"),1)]})],16,Ye)),i.isClearIconVisible?f(e.$slots,"clearicon",{key:2,class:x(e.cx("clearIcon")),clearCallback:i.onClearClick},function(){return[(r(),k(H(e.clearIcon?"i":"TimesIcon"),o({ref:"clearIcon",class:[e.cx("clearIcon"),e.clearIcon],onClick:i.onClearClick},e.ptm("clearIcon"),{"data-pc-section":"clearicon"}),null,16,["class","onClick"]))]}):O("",!0),b("div",o({class:e.cx("dropdown")},e.ptm("dropdown")),[e.loading?f(e.$slots,"loadingicon",{key:0,class:x(e.cx("loadingIcon"))},function(){return[e.loadingIcon?(r(),d("span",o({key:0,class:[e.cx("loadingIcon"),"pi-spin",e.loadingIcon],"aria-hidden":"true"},e.ptm("loadingIcon")),null,16)):(r(),k(c,o({key:1,class:e.cx("loadingIcon"),spin:"","aria-hidden":"true"},e.ptm("loadingIcon")),null,16,["class"]))]}):f(e.$slots,"dropdownicon",{key:1,class:x(e.cx("dropdownIcon"))},function(){return[(r(),k(H(e.dropdownIcon?"span":"ChevronDownIcon"),o({class:[e.cx("dropdownIcon"),e.dropdownIcon],"aria-hidden":"true"},e.ptm("dropdownIcon")),null,16,["class"]))]})],16),I(X,{appendTo:e.appendTo},{default:S(function(){return[I(ye,o({name:"p-connected-overlay",onEnter:i.onOverlayEnter,onAfterEnter:i.onOverlayAfterEnter,onLeave:i.onOverlayLeave,onAfterLeave:i.onOverlayAfterLeave},e.ptm("transition")),{default:S(function(){return[l.overlayVisible?(r(),d("div",o({key:0,ref:i.overlayRef,class:[e.cx("overlay"),e.panelClass,e.overlayClass],style:[e.panelStyle,e.overlayStyle],onClick:t[9]||(t[9]=function(){return i.onOverlayClick&&i.onOverlayClick.apply(i,arguments)}),onKeydown:t[10]||(t[10]=function(){return i.onOverlayKeyDown&&i.onOverlayKeyDown.apply(i,arguments)})},e.ptm("overlay")),[b("span",o({ref:"firstHiddenFocusableElementOnOverlay",role:"presentation","aria-hidden":"true",class:"p-hidden-accessible p-hidden-focusable",tabindex:0,onFocus:t[7]||(t[7]=function(){return i.onFirstHiddenFocus&&i.onFirstHiddenFocus.apply(i,arguments)})},e.ptm("hiddenFirstFocusableEl"),{"data-p-hidden-accessible":!0,"data-p-hidden-focusable":!0}),null,16),f(e.$slots,"header",{value:e.d_value,options:i.visibleOptions}),e.filter?(r(),d("div",o({key:0,class:e.cx("header")},e.ptm("header")),[I(N,{unstyled:e.unstyled,pt:e.ptm("pcFilterContainer")},{default:S(function(){return[I(w,{ref:"filterInput",type:"text",value:l.filterValue,onVnodeMounted:i.onFilterUpdated,onVnodeUpdated:i.onFilterUpdated,class:x(e.cx("pcFilter")),placeholder:e.filterPlaceholder,variant:e.variant,unstyled:e.unstyled,role:"searchbox",autocomplete:"off","aria-owns":e.$id+"_list","aria-activedescendant":i.focusedOptionId,onKeydown:i.onFilterKeyDown,onBlur:i.onFilterBlur,onInput:i.onFilterChange,pt:e.ptm("pcFilter"),formControl:{novalidate:!0}},null,8,["value","onVnodeMounted","onVnodeUpdated","class","placeholder","variant","unstyled","aria-owns","aria-activedescendant","onKeydown","onBlur","onInput","pt"]),I(K,{unstyled:e.unstyled,pt:e.ptm("pcFilterIconContainer")},{default:S(function(){return[f(e.$slots,"filtericon",{},function(){return[e.filterIcon?(r(),d("span",o({key:0,class:e.filterIcon},e.ptm("filterIcon")),null,16)):(r(),k(C,ge(o({key:1},e.ptm("filterIcon"))),null,16))]})]}),_:3},8,["unstyled","pt"])]}),_:3},8,["unstyled","pt"]),b("span",o({role:"status","aria-live":"polite",class:"p-hidden-accessible"},e.ptm("hiddenFilterResult"),{"data-p-hidden-accessible":!0}),v(i.filterResultMessageText),17)],16)):O("",!0),b("div",o({class:e.cx("listContainer"),style:{"max-height":i.virtualScrollerDisabled?e.scrollHeight:""}},e.ptm("listContainer")),[I(J,o({ref:i.virtualScrollerRef},e.virtualScrollerOptions,{items:i.visibleOptions,style:{height:e.scrollHeight},tabindex:-1,disabled:i.virtualScrollerDisabled,pt:e.ptm("virtualScroller")}),ve({content:S(function(u){var T=u.styleClass,Z=u.contentRef,V=u.items,h=u.getItemOptions,Q=u.contentStyle,M=u.itemSize;return[b("ul",o({ref:function(p){return i.listRef(p,Z)},id:e.$id+"_list",class:[e.cx("list"),T],style:Q,role:"listbox"},e.ptm("list")),[(r(!0),d(A,null,me(V,function(a,p){return r(),d(A,{key:i.getOptionRenderKey(a,i.getOptionIndex(p,h))},[i.isOptionGroup(a)?(r(),d("li",o({key:0,id:e.$id+"_"+i.getOptionIndex(p,h),style:{height:M?M+"px":void 0},class:e.cx("optionGroup"),role:"option",ref_for:!0},e.ptm("optionGroup")),[f(e.$slots,"optiongroup",{option:a.optionGroup,index:i.getOptionIndex(p,h)},function(){return[b("span",o({class:e.cx("optionGroupLabel"),ref_for:!0},e.ptm("optionGroupLabel")),v(i.getOptionGroupLabel(a.optionGroup)),17)]})],16,Qe)):Oe((r(),d("li",o({key:1,id:e.$id+"_"+i.getOptionIndex(p,h),class:e.cx("option",{option:a,focusedOption:i.getOptionIndex(p,h)}),style:{height:M?M+"px":void 0},role:"option","aria-label":i.getOptionLabel(a),"aria-selected":i.isSelected(a),"aria-disabled":i.isOptionDisabled(a),"aria-setsize":i.ariaSetSize,"aria-posinset":i.getAriaPosInset(i.getOptionIndex(p,h)),onClick:function(E){return i.onOptionSelect(E,a)},onMousemove:function(E){return i.onOptionMouseMove(E,i.getOptionIndex(p,h))},"data-p-selected":i.isSelected(a),"data-p-focused":l.focusedOptionIndex===i.getOptionIndex(p,h),"data-p-disabled":i.isOptionDisabled(a),ref_for:!0},i.getPTItemOptions(a,h,p,"option")),[e.checkmark?(r(),d(A,{key:0},[i.isSelected(a)?(r(),k(q,o({key:0,class:e.cx("optionCheckIcon"),ref_for:!0},e.ptm("optionCheckIcon")),null,16,["class"])):(r(),k(W,o({key:1,class:e.cx("optionBlankIcon"),ref_for:!0},e.ptm("optionBlankIcon")),null,16,["class"]))],64)):O("",!0),f(e.$slots,"option",{option:a,selected:i.isSelected(a),index:i.getOptionIndex(p,h)},function(){return[b("span",o({class:e.cx("optionLabel"),ref_for:!0},e.ptm("optionLabel")),v(i.getOptionLabel(a)),17)]})],16,_e)),[[Y]])],64)}),128)),l.filterValue&&(!V||V&&V.length===0)?(r(),d("li",o({key:0,class:e.cx("emptyMessage"),role:"option"},e.ptm("emptyMessage"),{"data-p-hidden-accessible":!0}),[f(e.$slots,"emptyfilter",{},function(){return[D(v(i.emptyFilterMessageText),1)]})],16)):!e.options||e.options&&e.options.length===0?(r(),d("li",o({key:1,class:e.cx("emptyMessage"),role:"option"},e.ptm("emptyMessage"),{"data-p-hidden-accessible":!0}),[f(e.$slots,"empty",{},function(){return[D(v(i.emptyMessageText),1)]})],16)):O("",!0)],16,Ze)]}),_:2},[e.$slots.loader?{name:"loader",fn:S(function(u){var T=u.options;return[f(e.$slots,"loader",{options:T})]}),key:"0"}:void 0]),1040,["items","style","disabled","pt"])],16),f(e.$slots,"footer",{value:e.d_value,options:i.visibleOptions}),!e.options||e.options&&e.options.length===0?(r(),d("span",o({key:1,role:"status","aria-live":"polite",class:"p-hidden-accessible"},e.ptm("hiddenEmptyMessage"),{"data-p-hidden-accessible":!0}),v(i.emptyMessageText),17)):O("",!0),b("span",o({role:"status","aria-live":"polite",class:"p-hidden-accessible"},e.ptm("hiddenSelectedMessage"),{"data-p-hidden-accessible":!0}),v(i.selectedMessageText),17),b("span",o({ref:"lastHiddenFocusableElementOnOverlay",role:"presentation","aria-hidden":"true",class:"p-hidden-accessible p-hidden-focusable",tabindex:0,onFocus:t[8]||(t[8]=function(){return i.onLastHiddenFocus&&i.onLastHiddenFocus.apply(i,arguments)})},e.ptm("hiddenLastFocusableEl"),{"data-p-hidden-accessible":!0,"data-p-hidden-focusable":!0}),null,16)],16)):O("",!0)]}),_:3},16,["onEnter","onAfterEnter","onLeave","onAfterLeave"])]}),_:3},8,["appendTo"])],16,Je)}We.render=et;export{We as s};
