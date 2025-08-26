import{I as L,H as l,a2 as C,a3 as f,a as c,b as d,f as m,F as b,h as v,l as S,i as g,t as p,D as P}from"./app-i_nL4kQ8.js";import{s as A}from"./index-JTdY4afz.js";var h=({dt:e})=>`
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
`,E={root:function(a){var i=a.props;return["p-steps p-component",{"p-readonly":i.readonly}]},list:"p-steps-list",item:function(a){var i=a.instance,t=a.item,s=a.index;return["p-steps-item",{"p-steps-item-active":i.isActive(s),"p-disabled":i.isItemDisabled(t,s)}]},itemLink:"p-steps-item-link",itemNumber:"p-steps-item-number",itemLabel:"p-steps-item-label"},V=L.extend({name:"steps",style:h,classes:E}),B={name:"BaseSteps",extends:A,props:{id:{type:String},model:{type:Array,default:null},readonly:{type:Boolean,default:!0},activeStep:{type:Number,default:0}},style:V,provide:function(){return{$pcSteps:this,$parentInstance:this}}},y={name:"Steps",extends:B,inheritAttrs:!1,emits:["update:activeStep","step-change"],data:function(){return{d_activeStep:this.activeStep}},watch:{activeStep:function(a){this.d_activeStep=a}},mounted:function(){var a=this.findFirstItem();a&&(a.tabIndex="0")},methods:{getPTOptions:function(a,i,t){return this.ptm(a,{context:{item:i,index:t,active:this.isActive(t),disabled:this.isItemDisabled(i,t)}})},onItemClick:function(a,i,t){if(this.disabled(i)||this.readonly){a.preventDefault();return}i.command&&i.command({originalEvent:a,item:i}),t!==this.d_activeStep&&(this.d_activeStep=t,this.$emit("update:activeStep",this.d_activeStep)),this.$emit("step-change",{originalEvent:a,index:t})},onItemKeydown:function(a,i){switch(a.code){case"ArrowRight":{this.navigateToNextItem(a.target),a.preventDefault();break}case"ArrowLeft":{this.navigateToPrevItem(a.target),a.preventDefault();break}case"Home":{this.navigateToFirstItem(a.target),a.preventDefault();break}case"End":{this.navigateToLastItem(a.target),a.preventDefault();break}case"Tab":break;case"Enter":case"NumpadEnter":case"Space":{this.onItemClick(a,i),a.preventDefault();break}}},navigateToNextItem:function(a){var i=this.findNextItem(a);i&&this.setFocusToMenuitem(a,i)},navigateToPrevItem:function(a){var i=this.findPrevItem(a);i&&this.setFocusToMenuitem(a,i)},navigateToFirstItem:function(a){var i=this.findFirstItem(a);i&&this.setFocusToMenuitem(a,i)},navigateToLastItem:function(a){var i=this.findLastItem(a);i&&this.setFocusToMenuitem(a,i)},findNextItem:function(a){var i=a.parentElement.nextElementSibling;return i?i.children[0]:null},findPrevItem:function(a){var i=a.parentElement.previousElementSibling;return i?i.children[0]:null},findFirstItem:function(){var a=f(this.$refs.list,'[data-pc-section="item"]');return a?a.children[0]:null},findLastItem:function(){var a=C(this.$refs.list,'[data-pc-section="item"]');return a?a[a.length-1].children[0]:null},setFocusToMenuitem:function(a,i){a.tabIndex="-1",i.tabIndex="0",i.focus()},isActive:function(a){return a===this.d_activeStep},isItemDisabled:function(a,i){return this.disabled(a)||this.readonly&&!this.isActive(i)},visible:function(a){return typeof a.visible=="function"?a.visible():a.visible!==!1},disabled:function(a){return typeof a.disabled=="function"?a.disabled():a.disabled},label:function(a){return typeof a.label=="function"?a.label():a.label},getMenuItemProps:function(a,i){var t=this;return{action:l({class:this.cx("itemLink"),onClick:function(n){return t.onItemClick(n,a)},onKeyDown:function(n){return t.onItemKeydown(n,a)}},this.getPTOptions("itemLink",a,i)),step:l({class:this.cx("itemNumber")},this.getPTOptions("itemNumber",a,i)),label:l({class:this.cx("itemLabel")},this.getPTOptions("itemLabel",a,i))}}}},z=["id"],M=["aria-current","onClick","onKeydown","data-p-active","data-p-disabled"];function T(e,a,i,t,s,n){return d(),c("nav",l({id:e.id,class:e.cx("root")},e.ptmi("root")),[m("ol",l({ref:"list",class:e.cx("list")},e.ptm("list")),[(d(!0),c(b,null,v(e.model,function(o,r){return d(),c(b,{key:n.label(o)+"_"+r.toString()},[n.visible(o)?(d(),c("li",l({key:0,class:[e.cx("item",{item:o,index:r}),o.class],style:o.style,"aria-current":n.isActive(r)?"step":void 0,onClick:function(u){return n.onItemClick(u,o,r)},onKeydown:function(u){return n.onItemKeydown(u,o,r)},ref_for:!0},n.getPTOptions("item",o,r),{"data-p-active":n.isActive(r),"data-p-disabled":n.isItemDisabled(o,r)}),[e.$slots.item?(d(),g(P(e.$slots.item),{key:1,item:o,index:r,active:r===s.d_activeStep,label:n.label(o),props:n.getMenuItemProps(o,r)},null,8,["item","index","active","label","props"])):(d(),c("span",l({key:0,class:e.cx("itemLink"),ref_for:!0},n.getPTOptions("itemLink",o,r)),[m("span",l({class:e.cx("itemNumber"),ref_for:!0},n.getPTOptions("itemNumber",o,r)),p(r+1),17),m("span",l({class:e.cx("itemLabel"),ref_for:!0},n.getPTOptions("itemLabel",o,r)),p(n.label(o)),17)],16))],16,M)):S("",!0)],64)}),128))],16)],16,z)}y.render=T;const N=[{id:"0",nombre:"Tarjeta de Identidad",code:"TI"},{id:"2",nombre:"Cédula Ciudadanía",code:"CC"},{id:"3",nombre:"Cédula Extranjería",code:"CE"}],R=[{id:21,comuna:"Altagracia",barrios:["Altagracia","Canaveral","El Estanquillo","El Jazmin","El Kiosko","Filobonito","Guadualito","La Linda","La Una","Tinajas"]},{id:22,comuna:"Arabia",barrios:["Arabia","Betulia","Betulia Alta","El Hogar","Miralindo","Perez Alto","Perez Bajo","Santa Cruz de Barba","Tres Esquinas","Yarumal"]},{id:17,comuna:"Boston",barrios:["Olaya Herrera","Centenario","Mejia Robledo","La Lorena IV","Los Profesionales","La Lorena III","La Lorena II","Belalcazar","La Lorena I","La Platanera","El Vergel","Travesuras - La Churria","Los Almendros","Pereira","Providencia","Verona","Vasconia","Venecia","Ciudad Palermo","Santa Catalina II","La Laguna","Bosques de La Salle","Boston","La Florida","Las Gaviotas","Central","Tulcan I","Tulcan II","Tulcan III","Villa Del Sol","San Remo","San Luis Gonzaga","Villa Colombia","Terminal","La Unidad","Ciudad Pereira","La Arboleda","Guaduales de Canaan","Caminos de Canaan"]},{id:23,comuna:"Caimalito",barrios:["Azufral","Caimalito","La Carbonera","La Paz"]},{id:1,comuna:"Centro",barrios:["Sector Lago Uribe","San Esteban","Sector Plaza de Bolivar","Primero de Febrero","Sector Galeria Central","Sector Parque La Libertad","Bloques Primero de Febrero","Turin","Venecia","Los Periodistas","La Victoria","Santander","Buenos Aires","Ricardo Ramirez Carmona","Las Garzas","La Paz","Sector 30 de Agosto","Los Nogales"]},{id:24,comuna:"Cerritos",barrios:["Belmonte Bajo","Cerritos","Esperanza Galicia","Estacion Villegas","Galicia Alta","Quimbayita"]},{id:27,comuna:"Combia Alta",barrios:["Alto Erazo","Amoladora Alta","Amoladora Baja","Betania","La Convencion","La Esperanza","Llano Grande","Minas del Socorro","Pital de Combia","San Luis","San Vicente"]},{id:25,comuna:"Combia Baja",barrios:["Crucero de Combia","El Chaquiro","El Eden","El Pomo","Honda","La Bodega","La Carmelita","La Renta","La Siria","La Suecia","Maracaibo","San Marino","Santander"]},{id:4,comuna:"Consota",barrios:["El Rosal","Los Nogales","El Futuro","Villa Elena","Antonio Jose de Sucre","Porvenir","Restrepo","Plan Camilo","La Divisa","Villa de La Paz","Vendedores Ambulantes","Villa Cecilia","Villa Consota","Villa Verde","El Palmar de Villa Verde","Multifamiliares  Villa Verde","Normandia","Panorama I","Villa Andrea","Dorado II","Las Piramides","Quintas de Panorama I","Antonio Jose Valencia","Sanai II","Dorado I","Quintas de Panorama II","Naranjito","Aguas Claras","Bella Sardi","El Paraiso","Miraflores *4","Mirador de Bella Sardi","Mirador de Naranjito","La Idalia II"]},{id:11,comuna:"Cuba",barrios:["San Fernando","Brisas Del Consota","La Playita","Cuba","Cortes","La Union","La Independencia *1","Rafael Uribe I","Barberos"]},{id:9,comuna:"Del Cafe",barrios:["Malaga *1","Altos de Llano Grande II *2","Mirador de Llano Grande","Altos de Los Angeles","Nuevo Horizonte","Ciudad Boquia *1","Sector B","Ciudadela Comfamiliar Boquia","Horizontes","Carlos Enrique Soto","Antonio Ricaurte","Rincon Del Cafe","Alamos Del Cafe","Villa Del Cafe","Paz Verde","Villa Los Comunales","Comfamiliar Boquia","Alameda del Cafe","Luis Alberto Duque","Portal de Llano Grande"]},{id:5,comuna:"El Jardín",barrios:["Brasilia","Los Cedros","Sector 30 de Agosto","Maraya","Caminos de Maraya","Alcazar de Maraya","Jardin I","Mayorca","Jardin de Velez Y Velez","Rincon de Las Quintas","Los Quimbayas","Las Mangas","Niza I","Brasilia","Niza II","La Elvira","Los Andes","Jardin II","Villas Del Jardin III","Bosques de Santa Elena I","Jardin III","Villas Del Jardin II","Villas Del Jardin I","Los Arrayanes","Portal de Los Cedros","Balcones Condominio","Cedritos","Altos de Tananbi","Jardines de Tanambi","Andalucia","Arco Iris de la Colina","La Castellana","Paseo de la Castellana","Conjunto Residencial Tisu"]},{id:10,comuna:"El Poblado",barrios:["Poblado II","Poblado I","Rocio Bajo","Villa Del Prado","Balcones de Villa Del Prado","Villa Del Palmar","Hamburgo","Barajas","Cachipay","Samaria I","Samaria II","Portal de San Jacinto"]},{id:19,comuna:"El Rocío",barrios:["Rocio Alto *3","Caracol La Curva *4"]},{id:7,comuna:"Ferrocarril",barrios:["Gilberto Pelßez","La Libertad","Jose Hilario Lopez II","Gabriel Trujillo","Simon Bolivar","Nacederos","Jose Hilario Lopez I","Sureste de la Sierra","El Plumon Alto","Matecana","El Plumon","El Plumon Bajo","Torres de San Mateo","Catalan","Portal de La Villa","La Hacienda","Nueva Esperanza"]},{id:28,comuna:"La Estrella -- La palmilla",barrios:["El Aguacate","El Contento","El Gurrio","La Estrella","La Mecenia","La Palmilla","La Selva"]},{id:26,comuna:"La Bella",barrios:["Canceles","El Chocho","El Rincon","La Bella","La Colonia","La Estrella Morron","Morron","Mundo Nuevo","Vista Hermosa"]},{id:29,comuna:"La Florida",barrios:["El Bosque","La Bananera","La Florida","La Laguna","La Suiza","Libare","Plan el Manzano","Porvenir","San Jose"]},{id:30,comuna:"Morelia",barrios:["Calle Larga","El Brillante","El Congolo","El Retiro","Frascate","La Bamba","Los Planes","Morelia","San Joaquin","Santa Teresa","Tres Puertas"]},{id:8,comuna:"Olímpica",barrios:["San Felipe","El Campin III","La Glorieta","Santa Monica","Villa Alicia","Alcazares","Altos de Belmonte","Canaveral II","Rincon de La Palma","El Pizamo","Pinar de Belmonte","Los Nogales","Los Arrevoles","Belmonte","El Palmar","Mirador de La Cien","Toluca","Samanes de Belmonte","El Campin I-II","Villa Ilusion","Multifamiliar La Villa","Obelisco de la villa","Villas de La Madrid","Gamma IV","Pinar de Gamma","La Villa","Gamma V","Santa Cruz de Gamma","Fegove","Gamma II","Alhambra","Rincon de La Villa","Alfa","Gamma III","Jardines de La Villa","Olimpico I","Olimpico II","Los Corales","Reserva de La Villa","Colores de La Villa","Alegrias de la Villa","Rivera Campestre","Coral Plaza","San Sebastian","El Palmar","Olivar de los Vientos II","Villasol Parque Residencial","Serrezuela","La Italia","Rincon de Unicentro","Senderos de Unicentro","Canaveral II","Canaveral","Belmonte B","Bosques de Cantabria"]},{id:18,comuna:"Oriente",barrios:["Paz Del Rio","Ormaza","Santander","La Pupi","20 de Julio","San Francisco","Hernando Velez Marulanda","Castano Robledo","Alfonso Lopez","Antonio Narino","Brisas Del Otun","Kennedy","El Pizamo","Cesar Nader Nader","Chico Restrepo","Arboleda Del Rio","Simon Bolivar","San Gregorio","La Rivera","Pimpollo - Libare","Altos Del Otun"]},{id:15,comuna:"Perla Del Otún",barrios:["La Francia","Gaviria Trujillo","La Albania","Heroes II","Metropolitano","Villa Kennedy","Heroes I","Departamento","Aranjuez","El Paraiso","Carlos Alberto Benavides","Villa Maria","Neyra Marquez","El Bosque","Sinai","La Policia","Villa Rocio","Los Almendros","Independientes"]},{id:32,comuna:"Puerto Caldas",barrios:["Puerto Caldas"]},{id:13,comuna:"Rio Otún",barrios:["Risaralda","Jaime Salazar Robledo","San Juan de Dios","Jose Antonio Galan","Nuevo Penol","Enrique Millan Rubio","La Sirena","San Jorge","Zea","El Progreso","San Juan","El Prado","Remigio Antonio Canarte","Santa Elena","Gualanday","La Esperanza","Santa Teresita","Salazar Londono","Jorge Eliecer Gaitan","Salvador Allende","Byron Gaviria","Jose Marti","Campina del Otun","Primero de Mayo","El Triunfo","Mirasol","Las Palmas","America","San Camilo","Getsemani","Colinas Del Triunfo","Constructores","San Antonio","Bavaria","La Palmera","Los Alcazares"]},{id:6,comuna:"San Joaquin",barrios:["Coralina","La Isla","Los Cisnes","Rafael Uribe II","Rafael Uribe III","Laureles I","El Crucero","Letras","Laureles II","Altos de Corales","Leningrado II","Plan Carvajal","Bello Horizonte","Santa Clara de Las Villas","Santa Juana de Las Villas","Portal de Corales","Leningrado III","Jose Maria Cordoba","Campo Alegre","Codelmar I","Perla Del Sur","Los Geranios","Los Conquistadores","Ciudadela Comfamiliar I","ciudadela salamanca","Puerta de Alcalá","Altavista","Alcala del Campo","Codelmar II","Atenas","Codelmar III","San Joaquin","Guayacanes","Codelmar IV","Ciudadela Comfamiliar II","El Eden","Los Girasoles","El Cardal","Portales de Birmania","El Recreo","Portal de San Joaquin II","Gibraltar","Portal de San Joaquin I","San Marcos","Tinajas","Bulevar del Bosque","Bulevar de las Villas","Bulevar del Cafe","Quintas de Corazal","EL Nogal Club Residencial"]},{id:14,comuna:"San Nicolas",barrios:["Las Antillas","Nuevo Mejico","San Nicolas","Villa Mery","Brisas de Las Americas","Villa Nohemy","San Martin de Loba","La Dulcera"]},{id:31,comuna:"Tribunas Córcega",barrios:["Alegrias","Altamira","Cantamonos","Caracol la Curva","Condina","El Guayabo","El Jordan","El Manzano","Guayabal","Huertas","La Graminea","Laguneta","Montelargo","Naranjito","Tribunas Consota","Tribunas Corcega","Yarumito"]},{id:2,comuna:"Universidad",barrios:["Popular Modelo","Los Rosales","La Aurora","San Jose Sur","San Jose","La Julia","Los Alpes","La Ensenanza","Cambulos","Pinares de San Martin","Quintanar Del Cerro","Los Angeles","La Julita","Los Alamos","Ciudad Jardin","Villa Los Alamos","Canaan","Favi Utp","La Sierra","La Parcela","Puerta de Abacanto","El Bosque","Altos de Canaan"]},{id:12,comuna:"Villa Santana",barrios:["Bellavista","Monserrate","Veracruz","Veracruz II","Nuevo Plan","San Vicente","Comfamiliar Villasantana","Intermedio","Las Margaritas","El Otono","La Isla","El Danubio","Ciudadela Tokio","Las Brisas","Remanso"]},{id:16,comuna:"Villavicencio",barrios:["Los Andes","Villavicencio","Corocito","Berlin","Los Andes"]},{id:3,comuna:"El Oso",barrios:["Los Cristales","Quintas de Los Sauces","Portal de Las Mercedes","Los Pinos","Sauces I","Cinco de Octubre","Sauces V","Alameda","Sauces II","Guadalupe","La Floresta","Sauces III","San Felipe","Altos de Panorama","Los Prados","Villa Elisa","Sauces IV","La Habana","El Acuario","Quintas de La Acuarela I","Santafe","Quintas de La Acuarela II","La Acuarela","Terranova","Torres de La Acuarela","Mirador de Panorama II","Villa Del Bosque","Mirador de Panorama I","Libertador","Hacienda Cuba","Pueblito Paisa","Libertador II","La Idalia","Mirador de Panorama III","Vista Hermosa","La Bretana","Even-Ezer","Nueva Colombia","Villa Ligia III","La Nueva Villa","Jaime Pardo Leal","Olimpia","Villa Ligia","Alejandria","Villa Del Sur","Villa Navarra","Montelibano","Porto Alegre","Porto Alegre Etp 2","Ciudadela Villa de Leyva","Guadacanal"]}],J=[{id:"0",nombre:"Sin condición",code:"NA"},{id:"1",nombre:"Persona con discapacidad",code:"discapacitado"},{id:"2",nombre:"Desplazados",code:"desplazados"},{id:"3",nombre:"Victimas",code:"victimasConfArm"},{id:"4",nombre:"Mujer cabeza de hogar",code:"mujerCabHogar"},{id:"5",nombre:"Padre cabeza de hogar",code:"hombreCabHogar"},{id:"6",nombre:"Habitante de calle",code:"habitanteCalle"},{id:"7",nombre:"Migrante",code:"migrante"}],G=[{id:"0",nombre:"No aplica",code:"NA"},{id:"1",nombre:"Mestizo",code:"mestizo"},{id:"2",nombre:"Afrocolombiano",code:"afro"},{id:"3",nombre:"Indígena",code:"indigena"},{id:"4",nombre:"Palanquero",code:"palanquero"},{id:"5",nombre:"Raizal",code:"raizal"},{id:"6",nombre:"ROM",code:"rom"}];export{R as b,J as c,G as e,y as s,N as t};
