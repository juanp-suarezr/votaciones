<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>

        <!-- ADMIN -->
        <div class="">

            <div v-if="eventos.length" class="flex flex-col justify-center">
                <div class="sm:flex justify-center items-center">
                    <select id="evento_selected" name="evento_selected" v-model="evento_selected"
                        @change="handleEnterKey"
                        class="block w-full p-2 text-base text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option selected value="" disabled>Elegir evento de votación</option>
                        <option class="flex cursor mb-2 sm:mb-auto" v-for="ev in eventos" :key="ev.id" :value="ev.id">
                            {{ ev.nombre }}
                        </option>
                    </select>

                </div>
                <div class="flex inline-flex justify-center gap-4 sm:gap-6 mb-4 mt-6 text-lg sm:mb-auto">
                    <button v-for="ev in eventos.find(item => item.id == evento_selected).tipos.split('|')" :key="ev"
                        class="flex cursor mb-2 sm:mb-auto" @click="showGraphics(ev)"
                        :class="ev == tipos ? 'underline text-blue-600' : 'text-gray-600'">
                        {{ ev }} |
                    </button>
                </div>

                <div class="sm:grid grid-cols-2 md:gap-6 sm:gap-4 mt-6">
                    <!-- votaciones -->
                    <div class="flex h-full m-auto flex-col justify-center mb-4 px-4">
                        <h2 class="text-gray-600 text-lg sm:text-xl font-bold text-center flex justify-center">
                            Resultado Votaciones
                        </h2>
                        <Chart type="bar" :data="chartData" :options="chartOptions" class="h-full w-full mt-4" />
                    </div>
                    <!-- nivel abstinencia -->
                    <div class="flex h-full m-auto flex-col justify-center mb-4 px-4">
                        <h2 class="text-gray-600 text-lg sm:text-xl font-bold text-center flex justify-center">
                            Nivel de votaciones
                        </h2>
                        <Chart type="doughnut" :data="chartData1" :options="chartOptions1" class="h-full w-full mt-4" />
                    </div>
                    <!-- table votantes -->
                    <div class="h-full mt-4 w-full col-span-2">
                        <div class="flex flex-col overflow-x-auto">
                            <div class="inline-block rounded-lg shadow">
                                <div class="inline-block min-w-full py-2">
                                    <div class="overflow-x-auto">
                                        <table class="w-full whitespace-no-wrap ">
                                            <thead>
                                                <tr
                                                    class="border-b bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                    <th colspan="2"
                                                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                                        Nombre
                                                    </th>

                                                    <th
                                                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                                        Identificación
                                                    </th>
                                                    <th
                                                        class="border-b-2 border-gray-200 bg-gray-100 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                                        votos
                                                    </th>


                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="user in candidatosXtipo" :key="user.id"
                                                    class="text-gray-700">
                                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                                        <Avatar v-if="user.votante.imagen == 'user.png'"
                                                            :label="getInitials(user.votante.nombre)"
                                                            class="bg-indigo-200 text-indigo-800 text-xl" size="xlarge"
                                                            shape="circle" />
                                                        <Avatar v-else :image="getImageUrl(user.votante.imagen)"
                                                            class="bg-indigo-200 text-indigo-800" size="xlarge"
                                                            shape="circle" />
                                                    </td>
                                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                                        <p class="text-gray-900 whitespace-no-wrap">{{ user.votante.nombre }}
                                                        </p>
                                                    </td>

                                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                                        <p class="text-gray-900 whitespace-no-wrap">{{
                                                            user.votante.identificacion }}</p>
                                                    </td>
                                                    <td class="border-b border-gray-200 bg-white px-5 py-5 text-sm">
                                                        <p class="text-gray-900 whitespace-no-wrap">{{ eventos.find(item => item.id == evento_selected).votos.filter(item => item.id_candidato == user.id_votante).length  }}</p>
                                                    </td>


                                                </tr>
                                            </tbody>
                                        </table>
                                        <div
                                            class="flex items-center border-b bg-gray-50 px-5 py-2 xs:flex-row justify-between text-gray-500 text-xs font-semibold">
                                            <button class="hover:scale-125 transition duration-500 cursor-pointer"
                                                @click="previousPage" :disabled="currentPage === 0"><i
                                                    class="fa-solid fa-arrow-left"></i></button>
                                            <p>Pagina {{ currentPage + 1 }} de {{ totalPages }}</p>
                                            <button class="hover:scale-125 transition duration-500 cursor-pointer"
                                                @click="nextPage" :disabled="currentPage === totalPages - 1"><i
                                                    class="fa-solid fa-arrow-right"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>



            </div>

            <div v-else class="flex justify-center">
                <Message severity="info" :sticky="sticky" class="text-center w-fit" :life="3000">El análisis de los
                    eventos de votación se
                    mostrará en esta sección</Message>
            </div>

        </div>



    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, inject, onMounted } from 'vue';
import Message from 'primevue/message';
import Chart from 'primevue/chart';
import Avatar from 'primevue/avatar';

const props = defineProps({
    eventos: Object,
    candidatos: Object,
    votantes: Object,

})

console.log(props);




//VARS PARA CHART   
const chartData = ref();
const chartOptions = ref();
const chartData1 = ref();
const chartOptions1 = ref();

//var de event select
const evento_selected = ref(props.eventos.length ? props.eventos[0].id : '');
//var de tipos
const tipos = ref(props.eventos.length ? props.eventos.find(item => item.id == evento_selected.value).tipos.split('|')[0] : 'NA');

//candidatos segun tipo
const candidatosXtipo = ref(props.candidatos.filter(item => {
    let res = item.id_evento == evento_selected.value;
    let res2 = item.tipo == tipos.value;
    return res && res2
}));

const showGraphics = tipo => {

    tipos.value = tipo;
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
    chartData1.value = setChartData1();
    chartOptions1.value = setChartOptions1();
    
    
    candidatosXtipo.value = props.candidatos.filter(item => {
        let res = item.id_evento == evento_selected.value;
        let res2 = item.tipo == tipos.value;
        return res && res2
    });
    console.log(candidatosXtipo.value);
    

}

//paginacion tutores
const itemsPerPage = ref(8);
const currentPage = ref(0);

const totalPages = computed(() => Math.ceil(candidatosXtipo.value.length / itemsPerPage.value));



const previousPage = () => {
    if (currentPage.value > 0) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value - 1) {
        currentPage.value++;
    }
};
//FIN Paginacion

watch(itemsPerPage, () => {
    currentPage.value = 0;
});

//MOSTRAR IMAGEN EN TABLA 
//IMAGEN 
const getImageUrl = (imageName) => {
    // Si las imágenes están almacenadas en la carpeta public/images, la ruta sería algo como esto:
    return `/storage/uploads/usuarios/${imageName}`;
};

//avatar letters
const getInitials = function (name) {
    let parts = name.split(' ');
    let initials = '';
    let count = 0;

    for (var i = 0; i < parts.length && count < 2; i++) {
        if (parts[i].length > 0 && parts[i] !== '') {
            initials += parts[i][0];
            count++;
        }
    }
    return initials;
};

watch(evento_selected, (value) => {


    tipos.value = props.eventos.length ? props.eventos.find(item => item.id == value).tipos.split('|')[0] : 'NA';
    showGraphics(tipos.value);


});



onMounted(() => {

    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
    chartData1.value = setChartData1();
    chartOptions1.value = setChartOptions1();

});

//chartdata
const setChartData = () => {

    const documentStyle = getComputedStyle(document.body);

    //Evento seleccionado
    let eventoSelect = props.eventos.find(item => item.id == evento_selected.value);


    //candidatos segun tipo
    let candidatosXtipo = props.candidatos.filter(item => {
        let res = item.id_evento == evento_selected.value;
        let res2 = item.tipo == tipos.value;
        return res && res2
    });

    //label con nombre 
    let labelsCandidatos = [];
    //Array con resultado votos
    let votosArray = [];

    //Para guardar el nombre de los candidatos y enciontrar votos por candidato
    candidatosXtipo.forEach(element => {
        labelsCandidatos.push(element.votante.nombre);

        votosArray.push(eventoSelect.votos.filter(item => item.id_candidato == element.id_votante).length)

    });



    return {

        labels: labelsCandidatos.join(', ').split(',').map(label => label.trim()),

        datasets: [
            {
                label: 'Votos',
                data: votosArray.join(', ').split(',').map(label => label.trim()),
                backgroundColor: [documentStyle.getPropertyValue('--gray-500')],
            }
        ]

    };

};

//set style chart
const setChartOptions = () => {
    const textColor = '#3b82f6';
    const textColorSecondary = '#000';
    const surfaceBorder = '#b0b6c0';

    return {
        indexAxis: 'x',
        maintainAspectRatio: true,
        aspectRatio: 1,
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

//chartdata1 votso- total de votantes hab
const setChartData1 = () => {

    const documentStyle = getComputedStyle(document.body);
    //Evento seleccionado
    let eventoSelect = props.eventos.find(item => item.id == evento_selected.value);

    //array resultados
    let resultadosArray = [];

    let votantes = props.votantes.filter(item => item.tipo == tipos.value && item.id_evento == eventoSelect.id).length;
    let votos = eventoSelect.votos.filter(item => item.tipo == tipos.value).length;

    resultadosArray.push(votantes - votos, votos)
    console.log(eventoSelect);
    

    return {

        labels: ['Abstinencia', 'votos realizados'],

        datasets: [
            {
                label: 'Cantidad',
                data: resultadosArray,
                backgroundColor: [documentStyle.getPropertyValue('--red-900'), documentStyle.getPropertyValue('--gray-500')],
                hoverBackgroundColor: [documentStyle.getPropertyValue('--red-800'), documentStyle.getPropertyValue('--gray-400')]
            }
        ]

    };

};

//set style chart1 votos - total votantes
const setChartOptions1 = () => {
    const textColor = '#3b82f6';
    const textColorSecondary = '#000';
    const surfaceBorder = '#b0b6c0';

    return {
        maintainAspectRatio: true,
        aspectRatio: 1,
        plugins: {
            legend: {
                labels: {
                    cutout: '60%',
                    color: textColorSecondary
                }
            }
        }
    };
}



</script>