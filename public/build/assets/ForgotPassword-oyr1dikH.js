import{T as d,k as f,o as m,c as i,a as e,u as t,w as o,F as _,Z as p,t as w,m as g,d as a,n as y,b as x,l as b}from"./app-M705427b.js";import{_ as k,a as h}from"./ApplicationLogo-rI4tL_3u.js";import{_ as v}from"./InputError-ep1vwne4.js";import{_ as V}from"./InputLabel-ogJd6lms.js";import{_ as $}from"./PrimaryButton-LB5zeq0Y.js";import{_ as F}from"./TextInput-YsE1rZnv.js";const N=a("div",{class:"mb-4 text-sm text-gray-600"}," Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one. ",-1),C={key:0,class:"mb-4 text-sm font-medium text-green-600"},j={class:"flex justify-end items-center mt-4"},z={__name:"ForgotPassword",props:{status:String},setup(l){const s=d({email:""}),n=()=>{s.post(route("password.email"))};return(B,r)=>{const c=f("Link");return m(),i(_,null,[e(t(p),{title:"Forgot Password"}),e(k,null,{default:o(()=>[e(c,{href:"/",class:"flex justify-center items-center mb-4"},{default:o(()=>[e(h,{class:"w-1/2 text-gray-500 fill-current"})]),_:1}),N,l.status?(m(),i("div",C,w(l.status),1)):g("",!0),a("form",{onSubmit:b(n,["prevent"])},[a("div",null,[e(V,{for:"email",value:"Email"}),e(F,{id:"email",type:"email",class:"block mt-1 w-full",modelValue:t(s).email,"onUpdate:modelValue":r[0]||(r[0]=u=>t(s).email=u),required:"",autofocus:"",autocomplete:"username"},null,8,["modelValue"]),e(v,{class:"mt-2",message:t(s).errors.email},null,8,["message"])]),a("div",j,[e($,{class:y(["w-full justify-center flex",{"opacity-25":t(s).processing}]),disabled:t(s).processing},{default:o(()=>[x(" Email Password Reset Link ")]),_:1},8,["class","disabled"])])],32)]),_:1})],64)}}};export{z as default};