// js/script_admin.js
document.addEventListener('DOMContentLoaded', () => {
    const apiBaseUrl = '../../../api/admin/'; // Ajusta si es necesario

    // --- Elementos del DOM ---
    // Login
    const loginForm = document.getElementById('loginForm');
    const loginMessage = document.getElementById('loginMessage');

    // Dashboard - General
    const adminUsernameSpan = document.getElementById('adminUsername');
    const mainContentTitle = document.getElementById('mainContentTitle');

    // Dashboard - Navegación
    const navDashboard = document.getElementById('navDashboard');
    const navRespuestas = document.getElementById('navRespuestas');
    const navReportes = document.getElementById('navReportes');
    const navConfiguracion = document.getElementById('navConfiguracion');
    const navCerrarSesion = document.getElementById('navCerrarSesion');

    // Dashboard - Contenedores de contenido
    const dynamicContentContainer = document.getElementById('dynamicContent');
    const dashboardHomeSections = [ // Secciones que se muestran en la vista principal del Dashboard
        document.getElementById('statsOverviewSection'),
        document.getElementById('filtersSectionDefault'),
        document.getElementById('chartsSection'),
        document.getElementById('recentNegativeComments'), // Podría ser parte del dashboard
        document.getElementById('actionsSectionDefault'),
        document.getElementById('alertsSectionDefault')
    ].filter(el => el != null); // Filtrar nulos si algún ID no existe

    const respuestasSection = document.getElementById('respuestasSection');
    const reportesSection = document.getElementById('reportesSection');
    const configuracionSection = document.getElementById('configuracionSection');

    // Dashboard - Estadísticas
    const statTotalRespuestas = document.getElementById('statTotalRespuestas');
    const statSatisfaccionPromedio = document.getElementById('statSatisfaccionPromedio');
    const statComentariosNegativosHoy = document.getElementById('statComentariosNegativosHoy');
    const statRespuestas7Dias = document.getElementById('statRespuestas7Dias');
    // Podrías añadir spans para las tendencias si las vas a popular

    // Dashboard - Respuestas
    const respuestasTableBody = document.getElementById('respuestasTableBody');
    const prevPageButton = document.getElementById('prevPageButton');
    const nextPageButton = document.getElementById('nextPageButton');
    const currentPageInfo = document.getElementById('currentPageInfo');
    const applyRespuestasFiltersButton = document.getElementById('applyRespuestasFilters');
    const filterAreaRespuestasSelect = document.getElementById('filterAreaRespuestas');


    // Dashboard - Configuración (Áreas)
    const newAreaNameInput = document.getElementById('newAreaName');
    const addAreaButton = document.getElementById('addAreaButton');
    const listaAreasAdminUl = document.getElementById('listaAreasAdmin');
    const filterAreaDashboardSelect = document.getElementById('filterAreaDashboard');


    let currentPage = 1;
    const limitPerPage = 10; // Cuántos items por página

    // --- Lógica de Autenticación y Sesión ---
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    } else {
        // Si no estamos en login.php, verificar sesión
        checkAdminSession();
    }

    async function handleLogin(event) {
        event.preventDefault();
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (!username || !password) {
            displayLoginMessage('Por favor, ingresa usuario y contraseña.', 'error');
            return;
        }

        try {
            const response = await fetch(`${apiBaseUrl}login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password }),
            });
            const data = await response.json();
            if (response.ok) {
                localStorage.setItem('adminUser', data.username); // Guardar nombre de usuario
                localStorage.setItem('isAdminLoggedIn', 'true'); // Marcar como logueado
                displayLoginMessage('Inicio de sesión exitoso. Redirigiendo...', 'success');
                window.location.href = 'dashboard.php';
            } else {
                displayLoginMessage(data.message || 'Error en el inicio de sesión.', 'error');
                localStorage.removeItem('isAdminLoggedIn');
                localStorage.removeItem('adminUser');
            }
        } catch (error) {
            console.error('Error en fetch login:', error);
            displayLoginMessage('Error de conexión al intentar iniciar sesión.', 'error');
        }
    }

    function displayLoginMessage(message, type) {
        if (loginMessage) {
            loginMessage.textContent = message;
            loginMessage.className = `login-message ${type}`;
        }
    }

    async function checkAdminSession() {
        // Primero, verificar el localStorage como una comprobación rápida del lado del cliente
        const isLoggedIn = localStorage.getItem('isAdminLoggedIn') === 'true';
        if (!isLoggedIn && !window.location.pathname.endsWith('login.php')) {
             console.log("No hay flag en localStorage, redirigiendo a login.");
            window.location.href = 'login.php';
            return;
        }

        // Luego, verificar con el servidor para mayor seguridad
        try {
            const response = await fetch(`${apiBaseUrl}verificar_sesion.php`);
            const data = await response.json();
            if (response.ok && data.loggedIn) {
                if (adminUsernameSpan) adminUsernameSpan.textContent = data.username || localStorage.getItem('adminUser') || 'Admin';
                localStorage.setItem('adminUser', data.username || 'Admin'); // Actualizar/confirmar usuario
                localStorage.setItem('isAdminLoggedIn', 'true');
                initializeDashboard(); // Si la sesión es válida, inicializar el dashboard
            } else {
                localStorage.removeItem('isAdminLoggedIn');
                localStorage.removeItem('adminUser');
                if (!window.location.pathname.endsWith('login.php')) {
                    console.log("Sesión no válida en servidor, redirigiendo a login.");
                    window.location.href = 'login.php';
                }
            }
        } catch (error) {
            console.error('Error verificando sesión:', error);
            // Si hay error de red y no estamos en login, podría ser problemático.
            // Por ahora, si hay flag en localStorage, permitimos continuar, pero esto podría mejorarse.
            if (!isLoggedIn && !window.location.pathname.endsWith('login.php')) {
                 window.location.href = 'login.php';
            } else if (isLoggedIn) {
                 if (adminUsernameSpan) adminUsernameSpan.textContent = localStorage.getItem('adminUser') || 'Admin';
                 initializeDashboard(); // Si hay error de red pero estaba logueado en LS, inicializar
            }
        }
    }

    async function handleLogout(event) {
        if(event) event.preventDefault();
        try {
            await fetch(`${apiBaseUrl}logout.php`, { method: 'POST' });
        } catch (error) {
            console.error('Error en fetch logout (se procederá a limpiar localmente):', error);
        } finally {
            localStorage.removeItem('isAdminLoggedIn');
            localStorage.removeItem('adminUser');
            window.location.href = 'login.php';
        }
    }

    // --- Inicialización del Dashboard y Navegación ---
    function initializeDashboard() {
        if (!document.body.classList.contains('dashboard-initialized')) { // Evitar reinicialización
            console.log("Inicializando Dashboard...");
            if (navDashboard) navDashboard.addEventListener('click', (e) => { e.preventDefault(); showDashboardHome(); });
            if (navRespuestas) navRespuestas.addEventListener('click', (e) => { e.preventDefault(); showRespuestas(); });
            if (navReportes) navReportes.addEventListener('click', (e) => { e.preventDefault(); showReportes(); });
            if (navConfiguracion) navConfiguracion.addEventListener('click', (e) => { e.preventDefault(); showConfiguracion(); });
            if (navCerrarSesion) navCerrarSesion.addEventListener('click', handleLogout);

            if(applyRespuestasFiltersButton) applyRespuestasFiltersButton.addEventListener('click', () => loadRespuestas(1)); // Reset a página 1 con filtros

            loadDashboardStats();
            loadAreasForFilters(); // Cargar áreas para los selectores de filtro
            // Cargar comentarios negativos recientes, alertas, etc. si es necesario para el home.

            document.body.classList.add('dashboard-initialized');
        }
        showDashboardHome(); // Mostrar la vista principal por defecto
    }

    function setActiveNav(activeLink) {
        document.querySelectorAll('.sidebar-nav a').forEach(link => link.classList.remove('active'));
        if (activeLink) activeLink.classList.add('active');
    }

    function hideAllSections() {
        [respuestasSection, reportesSection, configuracionSection, ...dashboardHomeSections].forEach(section => {
            if (section) section.style.display = 'none';
        });
    }

    function showDashboardHome() {
        hideAllSections();
        dashboardHomeSections.forEach(section => {
             if (section) section.style.display = section.tagName === 'SECTION' ? 'block' : (section.classList.contains('stats-overview') ? 'grid' : 'block'); // Ajustar display
        });
        if (mainContentTitle) mainContentTitle.textContent = 'Dashboard General';
        setActiveNav(navDashboard);
        loadDashboardStats(); // Recargar estadísticas al volver al dashboard
    }

    async function loadDashboardStats() {
        if (!statTotalRespuestas) return; // Si no estamos en una página con estos elementos.
        try {
            const response = await fetch(`${apiBaseUrl}estadisticas.php`);
            if (!response.ok) throw new Error(`HTTP error ${response.status}`);
            const stats = await response.json();

            if (statTotalRespuestas) statTotalRespuestas.textContent = stats.total_respuestas || 0;
            if (statSatisfaccionPromedio) statSatisfaccionPromedio.textContent = `${stats.satisfaccion_promedio || 0} / 5`;
            if (statComentariosNegativosHoy) statComentariosNegativosHoy.textContent = stats.comentarios_negativos_hoy || 0;
            if (statRespuestas7Dias) statRespuestas7Dias.textContent = stats.respuestas_ultimos_7_dias || 0;
            // Aquí podrías añadir lógica para las tendencias (stats.trend_respuestas_vs_ayer, etc.)
        } catch (error) {
            console.error("Error cargando estadísticas del dashboard:", error);
        }
    }

    async function loadAreasForFilters() {
        try {
            const response = await fetch(`${apiBaseUrl}areas.php`); // Asumiendo que areas.php devuelve todas las áreas para admin
            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
            const areas = await response.json();

            const populateSelect = (selectElement) => {
                if (!selectElement) return;
                selectElement.innerHTML = '<option value="all">Todas</option>'; // Opción por defecto
                if (areas && areas.length > 0) {
                    areas.forEach(area => {
                        const option = document.createElement('option');
                        option.value = area.id;
                        option.textContent = area.nombre;
                        selectElement.appendChild(option);
                    });
                }
            };
            populateSelect(filterAreaRespuestasSelect);
            populateSelect(filterAreaDashboardSelect); // Para filtros del dashboard si los tienes

        } catch (error) {
            console.error('Error al cargar áreas para filtros:', error);
        }
    }


    // --- Sección de Respuestas ---
    function showRespuestas() {
        hideAllSections();
        if (respuestasSection) respuestasSection.style.display = 'block';
        if (mainContentTitle) mainContentTitle.textContent = 'Listado de Respuestas';
        setActiveNav(navRespuestas);
        loadRespuestas(1); // Cargar primera página por defecto
    }

    async function loadRespuestas(page = 1) {
        if (!respuestasTableBody) return;
        currentPage = page;
        let queryParams = `?pagina=${page}&limit=${limitPerPage}`;

        // Recoger valores de los filtros de respuestas
        const areaId = filterAreaRespuestasSelect ? filterAreaRespuestasSelect.value : 'all';
        const fechaDesde = document.getElementById('filterDateFromRespuestas') ? document.getElementById('filterDateFromRespuestas').value : '';
        const fechaHasta = document.getElementById('filterDateToRespuestas') ? document.getElementById('filterDateToRespuestas').value : '';
        const calificacion = document.getElementById('filterRatingRespuestas') ? document.getElementById('filterRatingRespuestas').value : 'all';
        const keyword = document.getElementById('filterKeywordRespuestas') ? document.getElementById('filterKeywordRespuestas').value : '';

        if (areaId !== 'all') queryParams += `&area_id=${areaId}`;
        if (fechaDesde) queryParams += `&fecha_desde=${fechaDesde}`;
        if (fechaHasta) queryParams += `&fecha_hasta=${fechaHasta}`;
        if (calificacion !== 'all') queryParams += `&calificacion=${calificacion}`;
        if (keyword.trim()) queryParams += `&keyword=${encodeURIComponent(keyword.trim())}`;


        try {
            const response = await fetch(`${apiBaseUrl}respuestas.php${queryParams}`);
            if (!response.ok) throw new Error(`HTTP error ${response.status}`);
            const result = await response.json();

            respuestasTableBody.innerHTML = ''; // Limpiar tabla
            if (result.data && result.data.length > 0) {
                result.data.forEach(respuesta => {
                    const row = respuestasTableBody.insertRow();
                    row.insertCell().textContent = respuesta.id;
                    row.insertCell().textContent = new Date(respuesta.fecha).toLocaleString();
                    row.insertCell().textContent = respuesta.nombre_area || 'N/A';
                    row.insertCell().textContent = respuesta.nivel_satisfaccion;
                    const comentarioCell = row.insertCell();
                    comentarioCell.textContent = respuesta.comentario || '-';
                    comentarioCell.style.maxWidth = '300px'; // Para evitar comentarios muy largos
                    comentarioCell.style.whiteSpace = 'pre-wrap';
                    comentarioCell.style.wordBreak = 'break-word';
                    row.insertCell().textContent = respuesta.aspectos ? respuesta.aspectos.join(', ') : '-';
                });
            } else {
                respuestasTableBody.innerHTML = '<tr><td colspan="6">No se encontraron respuestas.</td></tr>';
            }
            updatePaginationControls(result.pagina, result.total_paginas);
        } catch (error) {
            console.error("Error cargando respuestas:", error);
            if (respuestasTableBody) respuestasTableBody.innerHTML = `<tr><td colspan="6">Error al cargar respuestas: ${error.message}</td></tr>`;
        }
    }

    function updatePaginationControls(paginaActual, totalPaginas) {
        if (!currentPageInfo) return;
        currentPageInfo.textContent = `Página ${paginaActual} de ${totalPaginas}`;
        prevPageButton.disabled = paginaActual <= 1;
        nextPageButton.disabled = paginaActual >= totalPaginas;

        prevPageButton.onclick = () => loadRespuestas(paginaActual - 1);
        nextPageButton.onclick = () => loadRespuestas(paginaActual + 1);
    }

    // --- Sección de Reportes ---
    function showReportes() {
        hideAllSections();
        if (reportesSection) reportesSection.style.display = 'block';
        if (mainContentTitle) mainContentTitle.textContent = 'Reportes y Gráficas';
        setActiveNav(navReportes);
        // Aquí llamarías a funciones para cargar datos para las gráficas
         loadChartData('distribucion_calificaciones', 'chartDistribucionCalificaciones');
         loadChartData('frecuencia_aspectos', 'chartFrecuenciaAspectos');
        console.log("Mostrando sección de reportes. Implementar carga de gráficas.");
    }

     async function loadChartData(tipoGrafica, elementIdToUpdateImage) {
    //     // Esta función es un ejemplo. Necesitarías una librería de gráficas (Chart.js)
    //     // o al menos una forma de mostrar los datos. Por ahora, solo un log.
       try {
           const response = await fetch(`${apiBaseUrl}datos_graficas.php?tipo=${tipoGrafica}`);
           if (!response.ok) throw new Error(`HTTP error ${response.status}`);
        const data = await response.json();
           console.log(`Datos para ${tipoGrafica}:`, data);
           const imgElement = document.getElementById(elementIdToUpdateImage)?.querySelector('img');
            if(imgElement) { /* Lógica para actualizar la imagen o renderizar la gráfica */ }
       } catch (error) {
        console.error(`Error cargando datos para gráfica ${tipoGrafica}:`, error);
    }
}


    // --- Sección de Configuración ---
    function showConfiguracion() {
        hideAllSections();
        if (configuracionSection) configuracionSection.style.display = 'block';
        if (mainContentTitle) mainContentTitle.textContent = 'Configuración del Sistema';
        setActiveNav(navConfiguracion);
        loadAreasAdmin(); // Cargar áreas para administrar
        if (addAreaButton) addAreaButton.onclick = handleAddArea;
    }

    async function loadAreasAdmin() {
        if (!listaAreasAdminUl) return;
        try {
            const response = await fetch(`${apiBaseUrl}areas.php`);
            if (!response.ok) throw new Error(`HTTP error ${response.status}`);
            const areas = await response.json();
            listaAreasAdminUl.innerHTML = '';
            if (areas && areas.length > 0) {
                areas.forEach(area => {
                    const li = document.createElement('li');
                    li.textContent = `${area.nombre} (ID: ${area.id})`;
                    // Añadir botones de editar/eliminar
                    const deleteBtn = document.createElement('button');
                    deleteBtn.textContent = 'Eliminar';
                    deleteBtn.classList.add('button-danger', 'button-small');
                    deleteBtn.onclick = () => handleDeleteArea(area.id, area.nombre);
                    li.appendChild(deleteBtn);
                    // Podrías añadir un botón de editar similarmente
                    listaAreasAdminUl.appendChild(li);
                });
            } else {
                listaAreasAdminUl.innerHTML = '<li>No hay áreas configuradas.</li>';
            }
        } catch (error) {
            console.error("Error cargando áreas para admin:", error);
            if (listaAreasAdminUl) listaAreasAdminUl.innerHTML = `<li>Error al cargar áreas: ${error.message}</li>`;
        }
    }

    async function handleAddArea() {
        if (!newAreaNameInput) return;
        const nombreArea = newAreaNameInput.value.trim();
        if (!nombreArea) {
            alert("El nombre del área no puede estar vacío.");
            return;
        }
        try {
            const response = await fetch(`${apiBaseUrl}areas.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre: nombreArea })
            });
            const result = await response.json();
            if (response.ok || response.status === 201) {
                alert(result.message || "Área agregada exitosamente.");
                newAreaNameInput.value = '';
                loadAreasAdmin(); // Recargar lista
            } else {
                alert(`Error: ${result.message || 'No se pudo agregar el área.'}`);
            }
        } catch (error) {
            console.error("Error agregando área:", error);
            alert("Error de conexión al agregar área.");
        }
    }

    async function handleDeleteArea(areaId, areaName) {
        if (!confirm(`¿Estás seguro de que quieres eliminar el área "${areaName}" (ID: ${areaId})? Esta acción no se puede deshacer.`)) {
            return;
        }
        try {
            // El ID se puede pasar en la URL o en el cuerpo. areas.php parece esperar ID en path o cuerpo.
            // Para simplicidad, si tu areas.php soporta DELETE con ID en el cuerpo:
            /*
            const response = await fetch(`${apiBaseUrl}areas.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: areaId })
            });
            */
            // O, si tu areas.php usa el ID en el path (ej. /api/admin/areas.php/123)
             const response = await fetch(`${apiBaseUrl}areas.php/${areaId}`, {
                method: 'DELETE'
            });

            const result = await response.json();
            if (response.ok) {
                alert(result.message || "Área eliminada exitosamente.");
                loadAreasAdmin(); // Recargar lista
            } else {
                alert(`Error: ${result.message || 'No se pudo eliminar el área.'}`);
            }
        } catch (error) {
            console.error("Error eliminando área:", error);
            alert("Error de conexión al eliminar área.");
        }
    }

});
