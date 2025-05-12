<?php  $titulo = 'Dashorad'; include_once '../templates/header_admin.php'; ?>



<div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Reactify Admin</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#" id="navDashboard" class="active">Dashboard</a></li>
                    <li><a href="#" id="navRespuestas">Respuestas</a></li>
                    <li><a href="#" id="navReportes">Reportes</a></li>
                    <li><a href="#" id="navConfiguracion">Configuración</a></li>
                    <li><a href="#" id="navCerrarSesion">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1 id="mainContentTitle">Dashboard General</h1>
                <div class="user-info">
                    <span id="adminUsername">Admin</span>
                    <img src="https://placehold.co/40x40/E0E0E0/777?text=User" alt="User Avatar" class="avatar" onerror="this.onerror=null; this.src='https://placehold.co/40x40/E0E0E0/777?text=Error';">
                </div>
            </header>

            <div id="dynamicContent">
                <section class="stats-overview" id="statsOverviewSection">
                    <div class="stat-card">
                        <h4>Total Respuestas</h4>
                        <p id="statTotalRespuestas">0</p>
                        <span class="trend positive" id="statTrendRespuestas"></span>
                    </div>
                    <div class="stat-card">
                        <h4>Satisfacción Promedio</h4>
                        <p id="statSatisfaccionPromedio">0 / 5</p>
                        <span class="trend" id="statTrendSatisfaccion"></span>
                    </div>
                    <div class="stat-card">
                        <h4>Comentarios Negativos Hoy</h4>
                        <p id="statComentariosNegativosHoy">0</p>
                        <span class="trend" id="statTrendComentariosNegativos"></span>
                    </div>
                    <div class="stat-card">
                        <h4>Respuestas Últimos 7 Días</h4>
                        <p id="statRespuestas7Dias">0</p>
                        <span class="trend" id="statTrendRespuestas7Dias"></span>
                    </div>
                </section>

                <section class="filters-section" id="filtersSectionDefault">
                    <h2>Filtros Dinámicos (Dashboard)</h2>
                    <div class="filters">
                        <div class="filter-group">
                            <label for="filterAreaDashboard">Área/Sucursal:</label>
                            <select id="filterAreaDashboard">
                                <option value="all">Todas</option>
                                </select>
                        </div>
                        <div class="filter-group">
                            <label for="filterDateDashboard">Fecha Desde:</label>
                            <input type="date" id="filterDateFromDashboard">
                        </div>
                        <div class="filter-group">
                            <label for="filterDateToDashboard">Fecha Hasta:</label>
                            <input type="date" id="filterDateToDashboard">
                        </div>
                        <button class="button-filter" id="applyDashboardFilters">Aplicar Filtros al Dashboard</button>
                    </div>
                </section>

                <section class="charts-section" id="chartsSection">
                    <div class="chart-container">
                        <h2>Satisfacción Promedio (Diaria/Semanal/Mensual)</h2>
                        <img src="https://placehold.co/600x300/EEEEEE/AAAAAA?text=Gráfica+Satisfacción" alt="Gráfica de Satisfacción" style="width:100%; max-width:600px;" id="imgGraficaSatisfaccion" onerror="this.onerror=null; this.src='https://placehold.co/600x300/EEEEEE/AAAAAA?text=Error+Cargando';">
                    </div>
                    <div class="chart-container">
                        <h2>Frecuencia por Aspecto Destacado/Mejorable</h2>
                        <img src="https://placehold.co/600x300/EEEEEE/AAAAAA?text=Gráfica+Aspectos" alt="Gráfica de Aspectos" style="width:100%; max-width:600px;" id="imgGraficaAspectos" onerror="this.onerror=null; this.src='https://placehold.co/600x300/EEEEEE/AAAAAA?text=Error+Cargando';">
                    </div>
                </section>
                </div>

            <section id="respuestasSection" class="content-section" style="display: none;">
                <h2>Listado de Respuestas</h2>
                <div class="filters-section">
                     <div class="filters">
                        <div class="filter-group">
                            <label for="filterAreaRespuestas">Área:</label>
                            <select id="filterAreaRespuestas">
                                <option value="all">Todas</option>
                                </select>
                        </div>
                        <div class="filter-group">
                            <label for="filterDateFromRespuestas">Desde:</label>
                            <input type="date" id="filterDateFromRespuestas">
                        </div>
                        <div class="filter-group">
                            <label for="filterDateToRespuestas">Hasta:</label>
                            <input type="date" id="filterDateToRespuestas">
                        </div>
                        <div class="filter-group">
                            <label for="filterRatingRespuestas">Calificación:</label>
                            <select id="filterRatingRespuestas">
                                <option value="all">Todas</option>
                                <option value="5">5 Estrellas</option>
                                <option value="4">4 Estrellas</option>
                                <option value="3">3 Estrellas</option>
                                <option value="2">2 Estrellas</option>
                                <option value="1">1 Estrella</option>
                            </select>
                        </div>
                         <div class="filter-group">
                            <label for="filterKeywordRespuestas">Palabra clave:</label>
                            <input type="text" id="filterKeywordRespuestas" placeholder="Ej: lento, problema">
                        </div>
                        <button class="button-filter" id="applyRespuestasFilters">Filtrar Respuestas</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="respuestasTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Área</th>
                                <th>Calificación</th>
                                <th>Comentario</th>
                                <th>Aspectos</th>
                            </tr>
                        </thead>
                        <tbody id="respuestasTableBody">
                            </tbody>
                    </table>
                </div>
                <div class="pagination-controls">
                    <button id="prevPageButton" disabled>Anterior</button>
                    <span id="currentPageInfo">Página 1 de 1</span>
                    <button id="nextPageButton" disabled>Siguiente</button>
                </div>
            </section>

            <section id="reportesSection" class="content-section" style="display: none;">
                 <h2>Reportes y Gráficas</h2>
                 <p>Esta sección mostrará gráficas detalladas.</p>
                 <div class="chart-container">
                    <h3>Distribución de Calificaciones</h3>
                    <div id="chartDistribucionCalificaciones">
                        <img src="https://placehold.co/600x300/EEEEEE/AAAAAA?text=Distribución+Calificaciones" alt="Gráfica Distribución Calificaciones" style="width:100%; max-width:600px;" onerror="this.onerror=null; this.src='https://placehold.co/600x300/EEEEEE/AAAAAA?text=Error+Cargando';">
                    </div>
                 </div>
                 <div class="chart-container">
                    <h3>Frecuencia de Aspectos</h3>
                     <div id="chartFrecuenciaAspectos">
                        <img src="https://placehold.co/600x300/EEEEEE/AAAAAA?text=Frecuencia+Aspectos" alt="Gráfica Frecuencia Aspectos" style="width:100%; max-width:600px;" onerror="this.onerror=null; this.src='https://placehold.co/600x300/EEEEEE/AAAAAA?text=Error+Cargando';">
                    </div>
                 </div>
            </section>

            <section id="configuracionSection" class="content-section" style="display: none;">
                <h2>Configuración del Sistema</h2>
                <p>Administración de áreas y otros parámetros.</p>
                <div id="adminAreas">
                    <h3>Gestionar Áreas</h3>
                    <div class="form-area-admin">
                        <input type="text" id="newAreaName" placeholder="Nombre de la nueva área">
                        <button id="addAreaButton">Agregar Área</button>
                    </div>
                    <h4>Listado de Áreas Existentes:</h4>
                    <ul id="listaAreasAdmin">
                        </ul>
                </div>
            </section>


            <section class="comments-section" id="recentNegativeComments" style="display:none;"> <h2>Últimos Comentarios Negativos</h2>
                <div class="comment-list" id="negativeCommentsList">
                    </div>
            </section>

            <section class="actions-section" id="actionsSectionDefault">
                <h2>Acciones</h2>
                <button class="button-export">Exportar a PDF</button>
                <button class="button-export">Exportar a Excel</button>
            </section>

             <section class="alerts-section" id="alertsSectionDefault">
                <h2>Alertas Recientes</h2>
                <div id="alertsList">
                    </div>
            </section>

        </main>
    </div>


     <?php include_once '../templates/footer_admin.php'; ?>