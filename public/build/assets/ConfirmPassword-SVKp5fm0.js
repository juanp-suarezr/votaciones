import{T as c,k as m,o as d,c as f,a as s,u as a,w as r,F as u,Z as p,d as o,n as _,b as w,l as b}from"./app-bVZP9eGc.js";import{_ as x,a as g}from"./ApplicationLogo-mO5GpIzX.js";import{_ as h,a as y,b as k}from"./TextInput-ItPSZo7M.js";import{_ as v}from"./PrimaryButton-lBzObyqm.js";const C=o("div",{class:"mb-4 text-sm text-gray-600"}," This is a secure area of the application. Please confirm your password before continuing. ",-1),V={class:"mt-4 flex justify-end"},j={__name:"ConfirmPassword",setup($){const e=c({password:""}),n=()=>{e.post(route("password.confirm"),{onFinish:()=>e.reset()})};return(P,t)=>{const l=m("Link");return d(),f(u,null,[s(a(p),{title:"Confirm Password"}),s(x,null,{default:r(()=>[s(l,{href:"/",class:"mb-4 flex items-center justify-center"},{default:r(()=>[s(g,{class:"h-10 w-10 fill-current text-gray-500"})]),_:1}),C,o("form",{onSubmit:b(n,["prevent"])},[o("div",null,[s(h,{for:"password",value:"Password"}),s(y,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:a(e).password,"onUpdate:modelValue":t[0]||(t[0]=i=>a(e).password=i),required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),s(k,{class:"mt-2",message:a(e).errors.password},null,8,["message"])]),o("div",V,[s(v,{class:_(["w-full",{"opacity-25":a(e).processing}]),disabled:a(e).processing},{default:r(()=>[w(" Confirm ")]),_:1},8,["class","disabled"])])],32)]),_:1})],64)}}};export{j as default};