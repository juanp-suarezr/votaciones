<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>

        <!-- votantes -->
        <div v-if="$page.props.user.roles.includes('Usuarios')" class="">

            <h2 class="text-gray-600 text-xl inline-flex">Votaciones pendientes</h2>
            <!-- eventos votaciones -->
            <div class="md:grid md:grid-cols-2 gap-4 mt-4">
                <div class="items-center px-4 m-auto" v-for="ev in eventos" :key="ev.id">
                    <div class="border-2 border-gray-400 p-2 border-dashed sm:flex"
                        v-if="ev.estado != 'Cerrado' && ev.votantes != null">
                        <p class="m-auto text-base pe-2">
                            {{ ev.nombre }}
                            <br>
                            <span class="text-sm text-gray-600">{{ ev.fecha_inicio }} - {{ ev.fecha_fin }}</span>
                            <br>
                            <span v-if="votos.find(item => item.id_eventos == ev.id)"
                                class="text-base text-gray-800 italic">
                                Usted ha votado por {{
                                    candidatos.find(item => item.id == votos.find(item => item.id_eventos ==
                                        ev.id).id_candidato).nombre
                                }}
                            </span>
                        </p>
                        <PrimaryLink v-if="ev.estado == 'Activo' && !votos.find(item => item.id_eventos == ev.id)"
                            class="md:text-base m-auto" :class="{ 'opacity-25': isLoading }" :disabled="isLoading"
                            :href="route('votos.index', { tipo_evento: ev.id, tipo_user: ev.votantes ? ev.votantes.tipo : '' })">
                            Votar
                        </PrimaryLink>
                    </div>
                </div>
            </div>

        </div>
        <!-- ADMIN -->
        <div v-if="$page.props.user.roles.includes('Administrador') || $page.props.user.roles.includes('Supervisor')" class="">

            <div v-if="eventos_admin.length" class="flex flex-col justify-center">
                <div class="sm:flex justify-center items-center">
                    <select id="evento_selected" name="evento_selected" v-model="evento_selected"
                        @change="handleEnterKey"
                        class="block w-full p-2 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option selected value="" disabled>Elegir evento de votación</option>
                        <option class="flex cursor mb-2 sm:mb-auto" v-for="eventos in eventos_admin" :key="eventos.id"
                            :value="eventos.id">
                            {{ eventos.nombre }}
                        </option>
                    </select>

                </div>

                <div class="sm:grid grid-cols-2 gap-4 mt-4">
                    <div
                        class="w-full flex flex-col justify-center rounded-xl p-2 shadow-xl flex m-auto h-full bg-gray-500 mb-4">
                        <h2 class="text-gray-200 text-lg sm:text-xl font-bold text-center flex justify-center">
                            Nivel de votaciones
                            <br>
                            Total Votantes habilitados - {{ info_events.votantes }}
                        </h2>
                        <Knob class="flex justify-center mt-4" v-model="info_events.votos" :size="200"
                            :max="info_events.votantes" />
                    </div>

                    <div
                        class="w-full flex flex-col justify-center rounded-xl p-2 shadow-xl flex m-auto h-full bg-gray-500">
                        <h2 class="text-gray-200 text-lg sm:text-xl font-bold text-center flex justify-center">
                            Total votos según tipo
                        </h2>
                        <Chart type="bar" :data="chartData" :options="chartOptions" class="h-full m-2" />
                    </div>
                </div>

            </div>

            <div v-else class="flex justify-center">
                <Message severity="info" :sticky="sticky" class="text-center w-fit" :life="3000">Los eventos de votación
                    activos serán
                    mostrados en esta sección</Message>
            </div>

        </div>




    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryLink from '@/Components/PrimaryLink.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, inject, onMounted } from 'vue';
import { useToast } from 'vue-toast-notification';
import Message from 'primevue/message';
import Chart from 'primevue/chart';
import Knob from 'primevue/knob';



const props = defineProps({
    eventos: Object,
    votos: Object,
    candidatos: Object,
    eventos_admin: Object,
    votantes: Object,
})

console.log(props);


onMounted(() => {

    chartData.value = setChartData();
    chartOptions.value = setChartOptions();

});





const $toast = useToast();

//VARS PARA CHART   
const chartData = ref();
const chartOptions = ref();

//para identificar routeo con error
const searchParams = new URLSearchParams(window.location.search);
const errorMessage = searchParams.get('error');


//var de event select
const evento_selected = ref(props.eventos_admin.length ? props.eventos_admin[0].id : '');

//tiempo carga milisegundos (5 min)
const reloadInterval = 5 * 60 * 1000;
//informacion de evento, segun seleccionado
const evento_info = ref(props.eventos_admin.length ? props.eventos_admin.find(item => item.id == evento_selected.value) : []);
//tipos de votantes segun evento

const tipos = evento_info.value.tipos != 'NA' ? evento_info.value.tipos.replace(/\s*\|\s*/g, ", ").split(", ") : 'NA';

//Mostrar votos segun tipo
const showVotosXtipo = () => {
    
    let votoXtipo = [];
    tipos.forEach(element => {
        votoXtipo.push(evento_info.value.votos.filter(item => item.tipo == element).length);

    });


    return votoXtipo.join(', ').split(',').map(label => label.trim());

}


//chartdata
const setChartData = () => {
    
    return {

        labels: transformLabels(evento_info.value.tipos != 'NA' ? evento_info.value.tipos.replace(/\s*\|\s*/g, ", ") : 'NA'),
        datasets: [
            {
                label: 'Votos',
                data:

                    showVotosXtipo(),

                backgroundColor: ['rgba(59, 130, 246, 1)'],
            }
        ]

    };

};

//set style chart
const setChartOptions = () => {
    const textColor = '#3b82f6';
    const textColorSecondary = '#fff';
    const surfaceBorder = '#000';

    return {
        indexAxis: 'y',
        maintainAspectRatio: false,
        aspectRatio: 2,
        plugins: {
            legend: {
                labels: {
                    color: textColorSecondary
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary,
                    font: {
                        weight: 500
                    }
                },
                grid: {
                    display: false,
                    drawBorder: true
                }
            },
            y: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: true
                }
            }
        }
    };
}



function transformLabels(labelString) {
    
    return labelString.split(',').map(label => label.trim());
}



const info_events = useForm
    ({
        votos: evento_selected.value.votos != null ? props.eventos_admin.find(item => item.id == evento_selected.value).votos.length : 0,
        votantes: props.votantes.filter(item => item.id_eventos == evento_selected.value).length
    });



if (errorMessage) {
    // Mensaje de error
    console.log(errorMessage);
    let instance = $toast.open({
        message: 'Usted ya ha realizado el voto, No puede volver a votar',
        type: 'error',
        position: 'top-right',
        duration: 8000,
        pauseOnHover: true,

    });
}

const handleEnterKey = () => {

    
    evento_info.value = props.eventos_admin.length ? props.eventos_admin.find(item => item.id == evento_selected.value) : [];
    
    info_events.votos = evento_selected.value.votos != null ? evento_info.value.votos.length : 0;
    info_events.votantes = props.votantes.filter(item => item.id_eventos == evento_selected.value).length;

    chartData.value = setChartData();
    chartOptions.value = setChartOptions();


}



</script>

<style>
.p-knob-text {
    fill: white !important;
}
</style>