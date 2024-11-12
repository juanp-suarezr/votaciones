<template>

    <Head title="Editar evento" />

    <AuthenticatedLayout>
        <template #header>
            Editar Evento {{ evento.id }}
        </template>

        <div class="flex flex-col bg-white border shadow-sm rounded-xl w-full">
            <div class="bg-gray-100 border-b rounded-t-xl py-3 px-4 md:py-4 md:px-5 grid grid-cols-2 gap-4">
                <h3 class="mt-1 text-gray-500">
                    {{ evento.nombre }}
                </h3>
                <div class="text-right">
                    <SecondaryLink :href="route('eventos.index')">
                        Regresar
                    </SecondaryLink>
                </div>

            </div>
            <div class="p-4 md:p-5">
                <form @submit.prevent="submit" class="grid sm:grid-cols-2 gap-4">
                    <!-- nombre -->
                    <div class="mb-2">
                        <InputLabel for="nombre" value="Nombre del evento" />
                        <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.nombre" required
                            autofocus autocomplete="nombre" />
                        <InputError class="mt-2" :message="form.errors.nombre" />
                    </div>

                    <!-- dependencias -->
                    <div class="mb-2">
                        <InputLabel for="dependencias" value="dependencias del evento" />
                        <TextInput id="dependencias" type="text" class="mt-1 block w-full" v-model="form.dependencias"
                            required autocomplete="dependencias" />
                        <InputError class="mt-2" :message="form.errors.dependencias" />
                    </div>

                    <!-- descripción -->
                    <div class="col-span-2 mb-2">
                        <p class="text-gray-600 text-sm">
                            Información adicional sobre el evento (max 300
                            caracteres)
                        </p>
                        <Textarea v-model="form.descripcion" variant="filled" autoResize rows="5"
                            class="mt-1 block w-full" required autocomplete="descripcion" />
                        <p class="text-gray-500 text-sm mt-2" :class="{ 'text-red-500': isMaxPalabras }">
                            {{ palabrasEnTestimonio }} caracteres <span v-if="isMaxPalabras">Limite de caracteres
                                superado</span>
                        </p>
                        <InputError class="mt-2" :message="form.errors.descripcion" />

                    </div>

                    <!-- tipos -->
                    <div class="col-span-2 mb-2 flex gap-4">
                        <InputLabel for="tipos" value="Tipos" />

                        <div class="card flex justify-content-center">
                            <MultiSelect id="tipos" v-model="form.tipos" display="chip" :options="Object.values(tipos)"
                                placeholder="Seleccione los tipos" :maxSelectedLabels="6" class="w-full md:w-20rem" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.tipos" />
                    </div>


                    <!-- fecha inicio -->
                    <div class="mb-2">
                        <Calendar v-model="form.fecha_inicio" showTime hourFormat="24" locale="es" dateFormat="dd/mm/yy"
                            variant="filled" showButtonBar placeholder="Fecha inicio"
                            class="my-4 flex justify-center" />
                        <InputError class="mt-2" :message="form.errors.fecha_inicio" />
                    </div>

                    <!-- fecha fin -->
                    <div class="mb-2">
                        <Calendar v-model="form.fecha_fin" showTime hourFormat="24" locale="es" dateFormat="dd/mm/yy"
                            variant="filled" showButtonBar placeholder="Fecha inicio"
                            class="my-4 flex justify-center" />
                        <InputError class="mt-2" :message="form.errors.fecha_fin" />
                    </div>

                    <!-- estado -->
                    <!-- estado -->
                    <div class="mb-2">
                        <InputLabel for="estado" value="Estado" />

                        <select id="estado" v-model="form.estado"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-auto p-2. px-4">
                            <option value="Activo">Activo</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Cerrado">Cerrado</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.estado" />
                    </div>



                    <div>
                        <div class="mt-4 flex flex-col items-end">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Guardar Evento
                            </PrimaryButton>

                        </div>
                    </div>

                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SecondaryButton from "@/Components/SecondaryButton.vue";
import MultiSelect from 'primevue/multiselect';
import { inject, ref, computed, watch } from 'vue';
import { PhotoIcon } from "@heroicons/vue/24/solid";
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import SecondaryLink from '@/Components/SecondaryLink.vue';





const swal = inject('$swal');

//variable de imagen
const imageUrl = ref(null);
//max palabras
const isMaxPalabras = ref(false);

const props = defineProps({
    evento: {
        type: Object,
        default: () => ({}),
    },
    tipos: {
        type: Object,
        default: () => ({}),
    },
});


const form = useForm({
    nombre: props.evento.nombre || '',
    descripcion: props.evento.descripcion || '',
    fecha_inicio: props.evento.fecha_inicio || '',
    fecha_fin: props.evento.fecha_fin || '',
    dependencias: props.evento.dependencias || '',
    tipos: props.evento.tipos != 'NA' ? props.evento.tipos.split('|') : '' || '',
    estado: props.evento.estado || '',
    id: props.evento.id,

});


// Limite palabras
const palabrasEnTestimonio = computed(() => {
    const palabras = form.descripcion.trim();
    const result = palabras.length;

    if (result >= 300) {
        isMaxPalabras.value = true;
    } else {
        isMaxPalabras.value = false;
    }

    return result;
});

watch(palabrasEnTestimonio, () => {
    form.descripcion = form.descripcion.trim(); // Asegúrate de que no haya espacios al principio o al final
});

const formatearFecha = fecha => {

    // Obtener los componentes de fecha y hora
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Agregar 1 al mes ya que en JavaScript los meses van de 0 a 11
    const día = String(fecha.getDate()).padStart(2, '0');
    const hora = String(fecha.getHours()).padStart(2, '0');
    const minuto = String(fecha.getMinutes()).padStart(2, '0');
    const segundo = String(fecha.getSeconds()).padStart(2, '0');

    // Formatear la fecha en el formato deseado
    return `${año}-${mes}-${día} ${hora}:${minuto}:${segundo}`;

}


const submit = () => {
    form.fecha_inicio = formatearFecha(new Date(form.fecha_inicio));
    form.fecha_fin = formatearFecha(new Date(form.fecha_fin));
    
    
    form.tipos = form.tipos == '' ? 'NA' :form.tipos.join("|");

    form.patch(route('eventos.update', props.evento.id), {
        onSuccess: () => swal({
            title: "Registro Actualizado",
            text: "El evento se ha actualizado exitosamente",
            icon: "success"
        }).then((result) => {
           window.location.reload();
        })
    });


};






</script>