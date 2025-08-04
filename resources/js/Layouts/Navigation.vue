<template>
    <div :class="$page.props.showingMobileMenu ? 'block' : 'hidden'" @click="$page.props.showingMobileMenu = false"
        class="fixed inset-0 z-20 bg-red-900 opacity-50 transition-opacity lg:hidden"></div>

    <div :class="$page.props.showingMobileMenu ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
        class="overflow-y-auto fixed inset-y-0 left-0 z-30 w-64 bg-gray-600 transition duration-300 transform lg:translate-x-0 lg:static lg:inset-0">
        <div class="flex justify-center items-center mt-4">
            <div class="flex items-center justify-center">
                <img :src="imglogo_w" class="w-4/6" alt="">

                <!-- <span class="mx-2 text-2xl font-semibold text-white">Administrador</span> -->
            </div>
        </div>

        <nav class="mt-6" x-data="{ isMultiLevelMenuOpen: false }">
            <SeparadorMenu>Menú</SeparadorMenu>
            <nav-link :href="route('dashboard')" :active="route().current('dashboard')">
                <HomeIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Inicio</span>
            </nav-link>
            <nav-link v-if="$page.props.user.roles.includes('Jurado')" :href="route('actaPresencial.create')" :active="route().current('actaPresencial')">
                <ClipboardDocumentIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Cargar acta escrutinio</span>
            </nav-link>

            <nav-link v-if="$page.props.user.roles.includes('Jurado') || $page.props.user.roles.includes('Administrador')" :href="route('votantesPresencial.index')" :active="route().current('votantesPresencial')">
                <ArchiveBoxArrowDownIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Reporte votantes</span>
            </nav-link>

            <nav-link v-if="$page.props.user.roles.includes('Jurado') || $page.props.user.roles.includes('Administrador')" :href="route('actaPresencial.index')" :active="route().current('actaPresencial')">
                <DocumentTextIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Reporte escrutinio</span>
            </nav-link>

            <SeparadorMenu v-if="$page.props.user.permissions.includes('eventos-editar')">Eventos</SeparadorMenu>
            <nav-link v-if="$page.props.user.permissions.includes('analisis-listar')" :href="route('analisis.index')"
                :active="route().current().includes('analisis')">
                <ChartBarIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Análisis de resultados </span>
            </nav-link>
            <nav-link v-if="$page.props.user.permissions.includes('eventos-editar')" :href="route('eventos.index')"
                :active="route().current().includes('eventos')">
                <ShareIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Eventos </span>
            </nav-link>

            <SeparadorMenu v-if="$page.props.user.permissions.includes('usuarios-listar')">Configuración</SeparadorMenu>
            <nav-link v-if="$page.props.user.permissions.includes('usuarios-listar')" :href="route('gestion_registros.index')" :active="route().current().includes('gestion_registros')">
                <CheckIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Gestión registros</span>
            </nav-link>
            <nav-link v-if="$page.props.user.permissions.includes('usuarios-listar')" :href="route('historial_registros')" :active="route().current().includes('historial_registros')">
                <CircleStackIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Historial registros</span>
            </nav-link>
            <nav-link v-if="$page.props.user.permissions.includes('candidatos-editar')" :href="route('candidatos.index')"
                :active="route().current().includes('candidatos')">
                <ClipboardDocumentIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Candidatos</span>
            </nav-link>
            <nav-link v-if="$page.props.user.permissions.includes('delegados-editar')" :href="route('delegados.index')"
                :active="route().current().includes('delegados')">
                <ClipboardDocumentIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Delegados/Jurados</span>
            </nav-link>
            <nav-link v-if="$page.props.user.permissions.includes('eventos-editar')" :href="route('proyectos.index')"
                :active="route().current().includes('proyectos')">
                <PaperClipIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Proyectos </span>
            </nav-link>
            <nav-link v-if="$page.props.user.permissions.includes('tipos-listar')" :href="route('tipos.index')"
                :active="route().current().includes('tipos')">
                <FireIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Tipos</span>
            </nav-link>
            <nav-link v-if="$page.props.user.permissions.includes('usuarios-listar')" :href="route('parametros.index')"
                :active="route().current().includes('parametros')">
                <AdjustmentsHorizontalIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Parametros</span>
            </nav-link>
            <nav-link v-if="$page.props.user.permissions.includes('usuarios-listar')" :href="route('users.index')"
                :active="route().current().includes('users')">
                <UserGroupIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Usuarios</span>
            </nav-link>

            <nav-link class="flex items-center " v-if="$page.props.user.permissions.includes('rol-listar')"
                :href="route('roles.index')" :active="route().current().includes('roles')">
                <FingerPrintIcon class="h-6 w-6 text-white" />
                <span class="mx-3">Roles</span>

            </nav-link>

        </nav>
    </div>
</template>

<script>
import NavLink from '@/Components/NavLink.vue';
import SeparadorMenu from "@/Components/SeparadorMenu.vue";
import { Link } from '@inertiajs/vue3';
import imglogo_w from '/public/assets/img/logo_white.png'
import { ref } from 'vue'
import { HomeIcon, ChartBarIcon, ShareIcon, ClipboardDocumentIcon, FireIcon, UserGroupIcon, FingerPrintIcon, AdjustmentsHorizontalIcon, PaperClipIcon, CheckIcon, CircleStackIcon, ArchiveBoxArrowDownIcon, DocumentTextIcon } from '@heroicons/vue/24/solid'

export default {
    components: {
        NavLink,
        Link,
        HomeIcon,
        ChartBarIcon,
        ShareIcon,
        ClipboardDocumentIcon,
        FireIcon,
        UserGroupIcon,
        FingerPrintIcon,
        AdjustmentsHorizontalIcon,
        PaperClipIcon,
        CheckIcon,
        CircleStackIcon,
        ArchiveBoxArrowDownIcon,
        DocumentTextIcon,
        SeparadorMenu
    },

    setup() {
        let showingTwoLevelMenu = ref(false)

        return {
            showingTwoLevelMenu,
            imglogo_w
        }
    },
}
</script>

<style scoped></style>
