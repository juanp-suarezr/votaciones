import{B as S,y as s,U as h,V as v,a as u,b as l,f as c,F as p,h as P,m as f,i as A,t as b,I as y}from"./app-dUjcy5ia.js";import{s as C}from"./index-DI_V581D.js";var T=({dt:a})=>`
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
    border-top: 2px solid ${a("steps.separator.background")};
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
    transition: outline-color ${a("steps.transition.duration")}, box-shadow ${a("steps.transition.duration")};
    border-radius: ${a("steps.item.link.border.radius")};
    outline-color: transparent;
    gap: ${a("steps.item.link.gap")};
}

.p-steps-item-link:not(.p-disabled):focus-visible {
    box-shadow: ${a("steps.item.link.focus.ring.shadow")};
    outline: ${a("steps.item.link.focus.ring.width")} ${a("steps.item.link.focus.ring.style")} ${a("steps.item.link.focus.ring.color")};
    outline-offset: ${a("steps.item.link.focus.ring.offset")};
}

.p-steps-item-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    color: ${a("steps.item.label.color")};
    display: block;
    font-weight: ${a("steps.item.label.font.weight")};
}

.p-steps-item-number {
    display: flex;
    align-items: center;
    justify-content: center;
    color: ${a("steps.item.number.color")};
    border: 2px solid ${a("steps.item.number.border.color")};
    background: ${a("steps.item.number.background")};
    min-width: ${a("steps.item.number.size")};
    height: ${a("steps.item.number.size")};
    line-height: ${a("steps.item.number.size")};
    font-size: ${a("steps.item.number.font.size")};
    z-index: 1;
    border-radius: ${a("steps.item.number.border.radius")};
    position: relative;
    font-weight: ${a("steps.item.number.font.weight")};
}

.p-steps-item-number::after {
    content: " ";
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: ${a("steps.item.number.border.radius")};
    box-shadow: ${a("steps.item.number.shadow")};
}

.p-steps:not(.p-readonly) .p-steps-item {
    cursor: pointer;
}

.p-steps-item-active .p-steps-item-number {
    background: ${a("steps.item.number.active.background")};
    border-color: ${a("steps.item.number.active.border.color")};
    color: ${a("steps.item.number.active.color")};
}

.p-steps-item-active .p-steps-item-label {
    color: ${a("steps.item.label.active.color")};
}
`,M={root:function(e){var n=e.props;return["p-steps p-component",{"p-readonly":n.readonly}]},list:"p-steps-list",item:function(e){var n=e.instance,r=e.item,d=e.index;return["p-steps-item",{"p-steps-item-active":n.isActive(d),"p-disabled":n.isItemDisabled(r,d)}]},itemLink:"p-steps-item-link",itemNumber:"p-steps-item-number",itemLabel:"p-steps-item-label"},L=S.extend({name:"steps",style:T,classes:M}),B={name:"BaseSteps",extends:C,props:{id:{type:String},model:{type:Array,default:null},readonly:{type:Boolean,default:!0},activeStep:{type:Number,default:0}},style:L,provide:function(){return{$pcSteps:this,$parentInstance:this}}},z={name:"Steps",extends:B,inheritAttrs:!1,emits:["update:activeStep","step-change"],data:function(){return{d_activeStep:this.activeStep}},watch:{activeStep:function(e){this.d_activeStep=e}},mounted:function(){var e=this.findFirstItem();e&&(e.tabIndex="0")},methods:{getPTOptions:function(e,n,r){return this.ptm(e,{context:{item:n,index:r,active:this.isActive(r),disabled:this.isItemDisabled(n,r)}})},onItemClick:function(e,n,r){if(this.disabled(n)||this.readonly){e.preventDefault();return}n.command&&n.command({originalEvent:e,item:n}),r!==this.d_activeStep&&(this.d_activeStep=r,this.$emit("update:activeStep",this.d_activeStep)),this.$emit("step-change",{originalEvent:e,index:r})},onItemKeydown:function(e,n){switch(e.code){case"ArrowRight":{this.navigateToNextItem(e.target),e.preventDefault();break}case"ArrowLeft":{this.navigateToPrevItem(e.target),e.preventDefault();break}case"Home":{this.navigateToFirstItem(e.target),e.preventDefault();break}case"End":{this.navigateToLastItem(e.target),e.preventDefault();break}case"Tab":break;case"Enter":case"NumpadEnter":case"Space":{this.onItemClick(e,n),e.preventDefault();break}}},navigateToNextItem:function(e){var n=this.findNextItem(e);n&&this.setFocusToMenuitem(e,n)},navigateToPrevItem:function(e){var n=this.findPrevItem(e);n&&this.setFocusToMenuitem(e,n)},navigateToFirstItem:function(e){var n=this.findFirstItem(e);n&&this.setFocusToMenuitem(e,n)},navigateToLastItem:function(e){var n=this.findLastItem(e);n&&this.setFocusToMenuitem(e,n)},findNextItem:function(e){var n=e.parentElement.nextElementSibling;return n?n.children[0]:null},findPrevItem:function(e){var n=e.parentElement.previousElementSibling;return n?n.children[0]:null},findFirstItem:function(){var e=v(this.$refs.list,'[data-pc-section="item"]');return e?e.children[0]:null},findLastItem:function(){var e=h(this.$refs.list,'[data-pc-section="item"]');return e?e[e.length-1].children[0]:null},setFocusToMenuitem:function(e,n){e.tabIndex="-1",n.tabIndex="0",n.focus()},isActive:function(e){return e===this.d_activeStep},isItemDisabled:function(e,n){return this.disabled(e)||this.readonly&&!this.isActive(n)},visible:function(e){return typeof e.visible=="function"?e.visible():e.visible!==!1},disabled:function(e){return typeof e.disabled=="function"?e.disabled():e.disabled},label:function(e){return typeof e.label=="function"?e.label():e.label},getMenuItemProps:function(e,n){var r=this;return{action:s({class:this.cx("itemLink"),onClick:function(i){return r.onItemClick(i,e)},onKeyDown:function(i){return r.onItemKeydown(i,e)}},this.getPTOptions("itemLink",e,n)),step:s({class:this.cx("itemNumber")},this.getPTOptions("itemNumber",e,n)),label:s({class:this.cx("itemLabel")},this.getPTOptions("itemLabel",e,n))}}}},E=["id"],G=["aria-current","onClick","onKeydown","data-p-active","data-p-disabled"];function I(a,e,n,r,d,i){return l(),u("nav",s({id:a.id,class:a.cx("root")},a.ptmi("root")),[c("ol",s({ref:"list",class:a.cx("list")},a.ptm("list")),[(l(!0),u(p,null,P(a.model,function(o,t){return l(),u(p,{key:i.label(o)+"_"+t.toString()},[i.visible(o)?(l(),u("li",s({key:0,class:[a.cx("item",{item:o,index:t}),o.class],style:o.style,"aria-current":i.isActive(t)?"step":void 0,onClick:function(m){return i.onItemClick(m,o,t)},onKeydown:function(m){return i.onItemKeydown(m,o,t)},ref_for:!0},i.getPTOptions("item",o,t),{"data-p-active":i.isActive(t),"data-p-disabled":i.isItemDisabled(o,t)}),[a.$slots.item?(l(),A(y(a.$slots.item),{key:1,item:o,index:t,active:t===d.d_activeStep,label:i.label(o),props:i.getMenuItemProps(o,t)},null,8,["item","index","active","label","props"])):(l(),u("span",s({key:0,class:a.cx("itemLink"),ref_for:!0},i.getPTOptions("itemLink",o,t)),[c("span",s({class:a.cx("itemNumber"),ref_for:!0},i.getPTOptions("itemNumber",o,t)),b(t+1),17),c("span",s({class:a.cx("itemLabel"),ref_for:!0},i.getPTOptions("itemLabel",o,t)),b(i.label(o)),17)],16))],16,G)):f("",!0)],64)}),128))],16)],16,E)}z.render=I;var k=({dt:a})=>`
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
    stroke: ${a("progressspinner.colorOne")};
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
        stroke: ${a("progressspinner.colorOne")};
    }
    40% {
        stroke: ${a("progressspinner.colorTwo")};
    }
    66% {
        stroke: ${a("progressspinner.colorThree")};
    }
    80%,
    90% {
        stroke: ${a("progressspinner.colorFour")};
    }
}
`,V={root:"p-progressspinner",spin:"p-progressspinner-spin",circle:"p-progressspinner-circle"},R=S.extend({name:"progressspinner",style:k,classes:V}),q={name:"BaseProgressSpinner",extends:C,props:{strokeWidth:{type:String,default:"2"},fill:{type:String,default:"none"},animationDuration:{type:String,default:"2s"}},style:R,provide:function(){return{$pcProgressSpinner:this,$parentInstance:this}}},F={name:"ProgressSpinner",extends:q,inheritAttrs:!1,computed:{svgStyle:function(){return{"animation-duration":this.animationDuration}}}},N=["fill","stroke-width"];function j(a,e,n,r,d,i){return l(),u("div",s({class:a.cx("root"),role:"progressbar"},a.ptmi("root")),[(l(),u("svg",s({class:a.cx("spin"),viewBox:"25 25 50 50",style:i.svgStyle},a.ptm("spin")),[c("circle",s({class:a.cx("circle"),cx:"50",cy:"50",r:"20",fill:a.fill,"stroke-width":a.strokeWidth,strokeMiterlimit:"10"},a.ptm("circle")),null,16,N)],16))],16)}F.render=j;const J=[{id:"0",nombre:"Tarjeta de Identidad",code:"TI"},{id:"2",nombre:"Cédula Ciudadanía",code:"CC"},{id:"3",nombre:"Cédula Extranjería",code:"CE"}],D=[{id:0,departamento:"Amazonas"},{id:1,departamento:"Antioquia"},{id:2,departamento:"Arauca"},{id:3,departamento:"Atlántico"},{id:4,departamento:"Bolívar"},{id:6,departamento:"Caldas"},{id:7,departamento:"Caquetá"},{id:8,departamento:"Casanare"},{id:9,departamento:"Cauca"},{id:10,departamento:"Cesar"},{id:11,departamento:"Chocó"},{id:12,departamento:"Cundinamarca"},{id:13,departamento:"Córdoba"},{id:14,departamento:"Guainía"},{id:15,departamento:"Guaviare"},{id:16,departamento:"Huila"},{id:17,departamento:"La Guajira"},{id:18,departamento:"Magdalena"},{id:19,departamento:"Meta"},{id:20,departamento:"Nariño"},{id:21,departamento:"Norte de Santander"},{id:22,departamento:"Putumayo"},{id:23,departamento:"Quindío"},{id:24,departamento:"Risaralda"},{id:25,departamento:"San Andrés y Providencia"},{id:26,departamento:"Santander"},{id:27,departamento:"Sucre"},{id:28,departamento:"Tolima"},{id:29,departamento:"Valle del Cauca"},{id:30,departamento:"Vaupés"},{id:31,departamento:"Vichada"}],O=[{id:0,departamento:"Amazonas",ciudades:["Leticia","Puerto Nariño"]},{id:1,departamento:"Antioquia",ciudades:["Abejorral","Abriaquí","Alejandría","Amagá","Amalfi","Andes","Angelópolis","Angostura","Anorí","Anzá","Apartadó","Arboletes","Argelia","Armenia","Barbosa","Bello","Belmira","Betania","Betulia","Briceño","Buriticá","Cáceres","Caicedo","Caldas","Campamento","Cañasgordas","Caracolí","Caramanta","Carepa","Carolina del Príncipe","Caucasia","Chigorodó","Cisneros","Ciudad Bolívar","Cocorná","Concepción","Concordia","Copacabana","Dabeiba","Donmatías","Ebéjico","El Bagre","El Carmen de Viboral","El Peñol","El Retiro","El Santuario","Entrerríos","Envigado","Fredonia","Frontino","Giraldo","Girardota","Gómez Plata","Granada","Guadalupe","Guarne","Guatapé","Heliconia","Hispania","Itagüí","Ituango","Jardín","Jericó","La Ceja","La Estrella","La Pintada","La Unión","Liborina","Maceo","Marinilla","Medellín","Montebello","Murindó","Mutatá","Nariño","Nechí","Necoclí","Olaya","Peque","Pueblorrico","Puerto Berrío","Puerto Nare","Puerto Triunfo","Remedios","Rionegro","Sabanalarga","Sabaneta","Salgar","San Andrés de Cuerquia","San Carlos","San Francisco","San Jerónimo","San José de la Montaña","San Juan de Urabá","San Luis","San Pedro de Urabá","San Pedro de los Milagros","San Rafael","San Roque","San Vicente","Santa Bárbara","Santa Fe de Antioquia","Santa Rosa de Osos","Santo Domingo","Segovia","Sonsón","Sopetrán","Támesis","Tarazá","Tarso","Titiribí","Toledo","Turbo","Uramita","Urrao","Valdivia","Valparaíso","Vegachí","Venecia","Vigía del Fuerte","Yalí","Yarumal","Yolombó","Yondó","Zaragoza"]},{id:2,departamento:"Arauca",ciudades:["Arauca","Arauquita","Cravo Norte","Fortul","Puerto Rondón","Saravena","Tame"]},{id:3,departamento:"Atlántico",ciudades:["Baranoa","Barranquilla","Campo de la Cruz","Candelaria","Galapa","Juan de Acosta","Luruaco","Malambo","Manatí","Palmar de Varela","Piojó","Polonuevo","Ponedera","Puerto Colombia","Repelón","Sabanagrande","Sabanalarga","Santa Lucía","Santo Tomás","Soledad","Suán","Tubará","Usiacurí"]},{id:4,departamento:"Bolívar",ciudades:["Achí","Altos del Rosario","Arenal","Arjona","Arroyohondo","Barranco de Loba","Brazuelo de Papayal","Calamar","Cantagallo","Cartagena de Indias","Cicuco","Clemencia","Córdoba","El Carmen de Bolívar","El Guamo","El Peñón","Hatillo de Loba","Magangué","Mahates","Margarita","María la Baja","Mompós","Montecristo","Morales","Norosí","Pinillos","Regidor","Río Viejo","San Cristóbal","San Estanislao","San Fernando","San Jacinto del Cauca","San Jacinto","San Juan Nepomuceno","San Martín de Loba","San Pablo","Santa Catalina","Santa Rosa","Santa Rosa del Sur","Simití","Soplaviento","Talaigua Nuevo","Tiquisio","Turbaco","Turbaná","Villanueva","Zambrano"]},{id:5,departamento:"Boyacá",ciudades:["Almeida","Aquitania","Arcabuco","Belén","Berbeo","Betéitiva","Boavita","Boyacá","Briceño","Buenavista","Busbanzá","Caldas","Campohermoso","Cerinza","Chinavita","Chiquinquirá","Chíquiza","Chiscas","Chita","Chitaraque","Chivatá","Chivor","Ciénega","Cómbita","Coper","Corrales","Covarachía","Cubará","Cucaita","Cuítiva","Duitama","El Cocuy","El Espino","Firavitoba","Floresta","Gachantivá","Gámeza","Garagoa","Guacamayas","Guateque","Guayatá","Güicán","Iza","Jenesano","Jericó","La Capilla","La Uvita","La Victoria","Labranzagrande","Macanal","Maripí","Miraflores","Mongua","Monguí","Moniquirá","Motavita","Muzo","Nobsa","Nuevo Colón","Oicatá","Otanche","Pachavita","Páez","Paipa","Pajarito","Panqueba","Pauna","Paya","Paz del Río","Pesca","Pisba","Puerto Boyacá","Quípama","Ramiriquí","Ráquira","Rondón","Saboyá","Sáchica","Samacá","San Eduardo","San José de Pare","San Luis de Gaceno","San Mateo","San Miguel de Sema","San Pablo de Borbur","Santa María","Santa Rosa de Viterbo","Santa Sofía","Santana","Sativanorte","Sativasur","Siachoque","Soatá","Socha","Socotá","Sogamoso","Somondoco","Sora","Soracá","Sotaquirá","Susacón","Sutamarchán","Sutatenza","Tasco","Tenza","Tibaná","Tibasosa","Tinjacá","Tipacoque","Toca","Togüí","Tópaga","Tota","Tunja","Tununguá","Turmequé","Tuta","Tutazá","Úmbita","Ventaquemada","Villa de Leyva","Viracachá","Zetaquira"]},{id:6,departamento:"Caldas",ciudades:["Aguadas","Anserma","Aranzazu","Belalcázar","Chinchiná","Filadelfia","La Dorada","La Merced","Manizales","Manzanares","Marmato","Marquetalia","Marulanda","Neira","Norcasia","Pácora","Palestina","Pensilvania","Riosucio","Risaralda","Salamina","Samaná","San José","Supía","Victoria","Villamaría","Viterbo"]},{id:7,departamento:"Caquetá",ciudades:["Albania","Belén de los Andaquíes","Cartagena del Chairá","Curillo","El Doncello","El Paujil","Florencia","La Montañita","Milán","Morelia","Puerto Rico","San José del Fragua","San Vicente del Caguán","Solano","Solita","Valparaíso"]},{id:8,departamento:"Casanare",ciudades:["Aguazul","Chámeza","Hato Corozal","La Salina","Maní","Monterrey","Nunchía","Orocué","Paz de Ariporo","Pore","Recetor","Sabanalarga","Sácama","San Luis de Palenque","Támara","Tauramena","Trinidad","Villanueva","Yopal"]},{id:9,departamento:"Cauca",ciudades:["Almaguer","Argelia","Balboa","Bolívar","Buenos Aires","Cajibío","Caldono","Caloto","Corinto","El Tambo","Florencia","Guachené","Guapí","Inzá","Jambaló","La Sierra","La Vega","López de Micay","Mercaderes","Miranda","Morales","Padilla","Páez","Patía","Piamonte","Piendamó","Popayán","Puerto Tejada","Puracé","Rosas","San Sebastián","Santa Rosa","Santander de Quilichao","Silvia","Sotará","Suárez","Sucre","Timbío","Timbiquí","Toribío","Totoró","Villa Rica"]},{id:10,departamento:"Cesar",ciudades:["Aguachica","Agustín Codazzi","Astrea","Becerril","Bosconia","Chimichagua","Chiriguaná","Curumaní","El Copey","El Paso","Gamarra","González","La Gloria (Cesar)","La Jagua de Ibirico","La Paz","Manaure Balcón del Cesar","Pailitas","Pelaya","Pueblo Bello","Río de Oro","San Alberto","San Diego","San Martín","Tamalameque","Valledupar"]},{id:11,departamento:"Chocó",ciudades:["Acandí","Alto Baudó","Bagadó","Bahía Solano","Bajo Baudó","Bojayá","Cantón de San Pablo","Cértegui","Condoto","El Atrato","El Carmen de Atrato","El Carmen del Darién","Istmina","Juradó","Litoral de San Juan","Lloró","Medio Atrato","Medio Baudó","Medio San Juan","Nóvita","Nuquí","Quibdó","Río Iró","Río Quito","Riosucio","San José del Palmar","Sipí","Tadó","Unión Panamericana","Unguía"]},{id:12,departamento:"Cundinamarca",ciudades:["Agua de Dios","Albán","Anapoima","Anolaima","Apulo","Arbeláez","Beltrán","Bituima","Bogotá","Bojacá","Cabrera","Cachipay","Cajicá","Caparrapí","Cáqueza","Carmen de Carupa","Chaguaní","Chía","Chipaque","Choachí","Chocontá","Cogua","Cota","Cucunubá","El Colegio","El Peñón","El Rosal","Facatativá","Fómeque","Fosca","Funza","Fúquene","Fusagasugá","Gachalá","Gachancipá","Gachetá","Gama","Girardot","Granada","Guachetá","Guaduas","Guasca","Guataquí","Guatavita","Guayabal de Síquima","Guayabetal","Gutiérrez","Jerusalén","Junín","La Calera","La Mesa","La Palma","La Peña","La Vega","Lenguazaque","Machetá","Madrid","Manta","Medina","Mosquera","Nariño","Nemocón","Nilo","Nimaima","Nocaima","Pacho","Paime","Pandi","Paratebueno","Pasca","Puerto Salgar","Pulí","Quebradanegra","Quetame","Quipile","Ricaurte","San Antonio del Tequendama","San Bernardo","San Cayetano","San Francisco","San Juan de Rioseco","Sasaima","Sesquilé","Sibaté","Silvania","Simijaca","Soacha","Sopó","Subachoque","Suesca","Supatá","Susa","Sutatausa","Tabio","Tausa","Tena","Tenjo","Tibacuy","Tibirita","Tocaima","Tocancipá","Topaipí","Ubalá","Ubaque","Ubaté","Une","Útica","Venecia","Vergara","Vianí","Villagómez","Villapinzón","Villeta","Viotá","Yacopí","Zipacón","Zipaquirá"]},{id:13,departamento:"Córdoba",ciudades:["Ayapel","Buenavista","Canalete","Cereté","Chimá","Chinú","Ciénaga de Oro","Cotorra","La Apartada","Lorica","Los Córdobas","Momil","Montelíbano","Montería","Moñitos","Planeta Rica","Pueblo Nuevo","Puerto Escondido","Puerto Libertador","Purísima","Sahagún","San Andrés de Sotavento","San Antero","San Bernardo del Viento","San Carlos","San José de Uré","San Pelayo","Tierralta","Tuchín","Valencia"]},{id:14,departamento:"Guainía",ciudades:["Inírida"]},{id:15,departamento:"Guaviare",ciudades:["Calamar","El Retorno","Miraflores","San José del Guaviare"]},{id:16,departamento:"Huila",ciudades:["Acevedo","Agrado","Aipe","Algeciras","Altamira","Baraya","Campoalegre","Colombia","El Pital","Elías","Garzón","Gigante","Guadalupe","Hobo","Íquira","Isnos","La Argentina","La Plata","Nátaga","Neiva","Oporapa","Paicol","Palermo","Palestina","Pitalito","Rivera","Saladoblanco","San Agustín","Santa María","Suaza","Tarqui","Tello","Teruel","Tesalia","Timaná","Villavieja","Yaguará"]},{id:17,departamento:"La Guajira",ciudades:["Albania","Barrancas","Dibulla","Distracción","El Molino","Fonseca","Hatonuevo","La Jagua del Pilar","Maicao","Manaure","Riohacha","San Juan del Cesar","Uribia","Urumita","Villanueva"]},{id:18,departamento:"Magdalena",ciudades:["Algarrobo","Aracataca","Ariguaní","Cerro de San Antonio","Chibolo","Chibolo","Ciénaga","Concordia","El Banco","El Piñón","El Retén","Fundación","Guamal","Nueva Granada","Pedraza","Pijiño del Carmen","Pivijay","Plato","Pueblo Viejo","Remolino","Sabanas de San Ángel","Salamina","San Sebastián de Buenavista","San Zenón","Santa Ana","Santa Bárbara de Pinto","Santa Marta","Sitionuevo","Tenerife","Zapayán","Zona Bananera"]},{id:19,departamento:"Meta",ciudades:["Acacías","Barranca de Upía","Cabuyaro","Castilla la Nueva","Cubarral","Cumaral","El Calvario","El Castillo","El Dorado","Fuente de Oro","Granada","Guamal","La Macarena","La Uribe","Lejanías","Mapiripán","Mesetas","Puerto Concordia","Puerto Gaitán","Puerto Lleras","Puerto López","Puerto Rico","Restrepo","San Carlos de Guaroa","San Juan de Arama","San Juanito","San Martín","Villavicencio","Vista Hermosa"]},{id:20,departamento:"Nariño",ciudades:["Aldana","Ancuyá","Arboleda","Barbacoas","Belén","Buesaco","Chachagüí","Colón","Consacá","Contadero","Córdoba","Cuaspud","Cumbal","Cumbitara","El Charco","El Peñol","El Rosario","El Tablón","El Tambo","Francisco Pizarro","Funes","Guachucal","Guaitarilla","Gualmatán","Iles","Imués","Ipiales","La Cruz","La Florida","La Llanada","La Tola","La Unión","Leiva","Linares","Los Andes","Magüí Payán","Mallama","Mosquera","Nariño","Olaya Herrera","Ospina","Pasto","Policarpa","Potosí","Providencia","Puerres","Pupiales","Ricaurte","Roberto Payán","Samaniego","San Bernardo","San José de Albán","San Lorenzo","San Pablo","San Pedro de Cartago","Sandoná","Santa Bárbara","Santacruz","Sapuyes","Taminango","Tangua","Tumaco","Túquerres","Yacuanquer"]},{id:21,departamento:"Norte de Santander",ciudades:["Ábrego","Arboledas","Bochalema","Bucarasica","Cáchira","Cácota","Chinácota","Chitagá","Convención","Cúcuta","Cucutilla","Duranía","El Carmen","El Tarra","El Zulia","Gramalote","Hacarí","Herrán","La Esperanza","La Playa de Belén","Labateca","Los Patios","Lourdes","Mutiscua","Ocaña","Pamplona","Pamplonita","Puerto Santander","Ragonvalia","Salazar de Las Palmas","San Calixto","San Cayetano","Santiago","Santo Domingo de Silos","Sardinata","Teorama","Tibú","Toledo","Villa Caro","Villa del Rosario"]},{id:22,departamento:"Putumayo",ciudades:["Colón","Mocoa","Orito","Puerto Asís","Puerto Caicedo","Puerto Guzmán","Puerto Leguízamo","San Francisco","San Miguel","Santiago","Sibundoy","Valle del Guamuez","Villagarzón"]},{id:23,departamento:"Quindío",ciudades:["Armenia","Buenavista","Calarcá","Circasia","Córdoba","Filandia","Génova","La Tebaida","Montenegro","Pijao","Quimbaya","Salento"]},{id:24,departamento:"Risaralda",ciudades:["Apía","Balboa","Belén de Umbría","Dosquebradas","Guática","La Celia","La Virginia","Marsella","Mistrató","Pereira","Pueblo Rico","Quinchía","Santa Rosa de Cabal","Santuario"]},{id:25,departamento:"San Andrés y Providencia",ciudades:["Providencia y Santa Catalina Islas","San Andrés"]},{id:26,departamento:"Santander",ciudades:["Aguada","Albania","Aratoca","Barbosa","Barichara","Barrancabermeja","Betulia","Bolívar","Bucaramanga","Cabrera","California","Capitanejo","Carcasí","Cepitá","Cerrito","Charalá","Charta","Chima","Chipatá","Cimitarra","Concepción","Confines","Contratación","Coromoro","Curití","El Carmen de Chucurí","El Guacamayo","El Peñón","El Playón","El Socorro","Encino","Enciso","Florián","Floridablanca","Galán","Gámbita","Girón","Guaca","Guadalupe","Guapotá","Guavatá","Güepsa","Hato","Jesús María","Jordán","La Belleza","La Paz","Landázuri","Lebrija","Los Santos","Macaravita","Málaga","Matanza","Mogotes","Molagavita","Ocamonte","Oiba","Onzaga","Palmar","Palmas del Socorro","Páramo","Piedecuesta","Pinchote","Puente Nacional","Puerto Parra","Puerto Wilches","Rionegro","Sabana de Torres","San Andrés","San Benito","San Gil","San Joaquín","San José de Miranda","San Miguel","San Vicente de Chucurí","Santa Bárbara","Santa Helena del Opón","Simacota","Suaita","Sucre","Suratá","Tona","Valle de San José","Vélez","Vetas","Villanueva","Zapatoca"]},{id:27,departamento:"Sucre",ciudades:["Buenavista","Caimito","Chalán","Colosó","Corozal","Coveñas","El Roble","Galeras","Guaranda","La Unión","Los Palmitos","Majagual","Morroa","Ovejas","Sampués","San Antonio de Palmito","San Benito Abad","San Juan de Betulia","San Marcos","San Onofre","San Pedro","Sincé","Sincelejo","Sucre","Tolú","Tolú Viejo"]},{id:28,departamento:"Tolima",ciudades:["Alpujarra","Alvarado","Ambalema","Anzoátegui","Armero","Ataco","Cajamarca","Carmen de Apicalá","Casabianca","Chaparral","Coello","Coyaima","Cunday","Dolores","El Espinal","Falán","Flandes","Fresno","Guamo","Herveo","Honda","Ibagué","Icononzo","Lérida","Líbano","Mariquita","Melgar","Murillo","Natagaima","Ortega","Palocabildo","Piedras","Planadas","Prado","Purificación","Rioblanco","Roncesvalles","Rovira","Saldaña","San Antonio","San Luis","Santa Isabel","Suárez","Valle de San Juan","Venadillo","Villahermosa","Villarrica"]},{id:29,departamento:"Valle del Cauca",ciudades:["Alcalá","Andalucía","Ansermanuevo","Argelia","Bolívar","Buenaventura","Buga","Bugalagrande","Caicedonia","Cali","Calima","Candelaria","Cartago","Dagua","El Águila","El Cairo","El Cerrito","El Dovio","Florida","Ginebra","Guacarí","Jamundí","La Cumbre","La Unión","La Victoria","Obando","Palmira","Pradera","Restrepo","Riofrío","Roldanillo","San Pedro","Sevilla","Toro","Trujillo","Tuluá","Ulloa","Versalles","Vijes","Yotoco","Yumbo","Zarzal"]},{id:30,departamento:"Vaupés",ciudades:["Carurú","Mitú","Taraira"]},{id:31,departamento:"Vichada",ciudades:["Cumaribo","La Primavera","Puerto Carreño","Santa Rosalía"]}],x=[{id:"0",nombre:"Sin condición",code:"NA"},{id:"1",nombre:"Persona con discapacidad",code:"discapacitado"},{id:"2",nombre:"Desplazados",code:"desplazados"},{id:"3",nombre:"Victimas",code:"victimasConfArm"},{id:"4",nombre:"Mujer cabeza de hogar",code:"mujerCabHogar"},{id:"5",nombre:"Padre cabeza de hogar",code:"hombreCabHogar"},{id:"6",nombre:"Habitante de calle",code:"habitanteCalle"},{id:"7",nombre:"Migrante",code:"migrante"}],U=[{id:"0",nombre:"No aplica",code:"NA"},{id:"1",nombre:"Mestizo",code:"mestizo"},{id:"2",nombre:"Afrocolombiano",code:"afro"},{id:"3",nombre:"Indígena",code:"indigena"},{id:"4",nombre:"Palanquero",code:"palanquero"},{id:"5",nombre:"Raizal",code:"raizal"},{id:"6",nombre:"ROM",code:"rom"}],H="/build/assets/cedulaFrontEjemplo-DGwvICV4.webp",Q="/build/assets/cedulaBackEjemplo-Bxw46J0A.webp";export{x as a,Q as b,O as c,D as d,U as e,H as f,F as g,z as s,J as t};
