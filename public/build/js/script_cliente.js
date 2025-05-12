// js/script_cliente.js
document.addEventListener('DOMContentLoaded', () => {
    const emojiOptions = document.querySelectorAll('.emoji-option');
    const satisfactionLevelInput = document.getElementById('satisfactionLevel');
    const areaSelect = document.getElementById('area');
    const nextButton = document.getElementById('nextButton');

    // URLs de las páginas del flujo y API
        const aspectosPage = '/aspectos';
        const comentarioPage = '/comentario';
        const agradecimientoPage = '/agradecimientos';
        const apiBaseUrl = '/cliente/'; // Ajusta si tu estructura de carpetas es diferente

    let surveyData = {
        satisfactionLevel: null,
        selectedAreaId: null,
        selectedAspects: [],
        comment: ''
    };

    // --- Lógica para index.php ---
    if (document.querySelector('.satisfaction-scale') && areaSelect) {
        loadAreas(); // Cargar áreas al iniciar

        emojiOptions.forEach(option => {
            option.addEventListener('click', () => {
                emojiOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                surveyData.satisfactionLevel = option.getAttribute('data-value');
                satisfactionLevelInput.value = surveyData.satisfactionLevel;
                checkIndexFormCompletion();
            });
        });

        areaSelect.addEventListener('change', () => {
            surveyData.selectedAreaId = areaSelect.value;
            checkIndexFormCompletion();
        });

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                if (surveyData.satisfactionLevel && surveyData.selectedAreaId) {
                    // Guardar temporalmente en localStorage para pasar entre páginas si es necesario,
                    // o construir el objeto de datos directamente si se envía todo al final.
                    localStorage.setItem('surveyDataTemp', JSON.stringify(surveyData));

                    const satisfaction = parseInt(surveyData.satisfactionLevel, 10);
                    if (satisfaction <= 2) {
                        window.location.href = comentarioPage;
                    } else {
                        window.location.href = aspectosPage;
                    }
                }
            });
        }
    }

    function checkIndexFormCompletion() {
        if (surveyData.satisfactionLevel && surveyData.selectedAreaId && surveyData.selectedAreaId !== "") {
            nextButton.disabled = false;
        } else {
            nextButton.disabled = true;
        }
    }

   async function loadAreas() {
    try {
        const response = await fetch(`${apiBaseUrl}obtener_areas`);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const areas = await response.json();
        if (areas && areas.length > 0) {
            areaSelect.innerHTML = '<option value="">Selecciona un área...</option>'; // Limpiar y añadir opción por defecto
            areas.forEach(area => {
                const option = document.createElement('option');
                option.value = area.id;
                option.textContent = area.nombre;
                areaSelect.appendChild(option);
            });
        } else {
            areaSelect.innerHTML = '<option value="">No hay áreas disponibles</option>';
        }
    } catch (error) {
        console.error('Error al cargar las áreas:', error);
        areaSelect.innerHTML = '<option value="">Error al cargar áreas</option>';
    }
}

    // --- Lógica para la página de aspectos.php ---
    const aspectsContainer = document.getElementById('aspectsContainer');
    const submitAspectsButton = document.getElementById('submitAspectsButton');

    if (aspectsContainer && submitAspectsButton) {
        // Recuperar datos de localStorage
        const storedData = localStorage.getItem('surveyDataTemp');
        if (storedData) {
            surveyData = JSON.parse(storedData);
        }

        const selectedAspectsSet = new Set(surveyData.selectedAspects || []);

        aspectsContainer.querySelectorAll('.aspect-button').forEach(button => {
            // Marcar botones si ya estaban seleccionados (ej. al volver atrás)
            if (selectedAspectsSet.has(button.getAttribute('data-aspect'))) {
                button.classList.add('selected');
            }

            button.addEventListener('click', () => {
                button.classList.toggle('selected');
                const aspectValue = button.getAttribute('data-aspect');
                if (button.classList.contains('selected')) {
                    selectedAspectsSet.add(aspectValue);
                } else {
                    selectedAspectsSet.delete(aspectValue);
                }
            });
        });

        submitAspectsButton.addEventListener('click', async () => {
            surveyData.selectedAspects = [...selectedAspectsSet];
            await submitSurvey();
        });
    }

    // --- Lógica para la página de comentario.php ---
    const commentForm = document.getElementById('commentForm');
    const commentTextarea = document.getElementById('commentText');
    const submitCommentButton = document.getElementById('submitCommentButton');

    if (commentForm && submitCommentButton) {
        // Recuperar datos de localStorage
        const storedData = localStorage.getItem('surveyDataTemp');
        if (storedData) {
            surveyData = JSON.parse(storedData);
        }
        if (surveyData.comment) { // Rellenar si ya existe comentario (ej. al volver atrás)
            commentTextarea.value = surveyData.comment;
        }


        submitCommentButton.addEventListener('click', async () => {
            surveyData.comment = commentTextarea.value.trim();
            await submitSurvey();
        });
    }

    // --- Función para enviar la encuesta completa ---
   async function submitSurvey() {
    const dataToSend = {
        nivel_satisfaccion: parseInt(surveyData.satisfactionLevel, 10),
        areas_id: parseInt(surveyData.selectedAreaId, 10),
        comentario: surveyData.comment || null, // Enviar null si está vacío
        aspectos: surveyData.selectedAspects || []
    };

    try {
        const response = await fetch(`${apiBaseUrl}guardar_respuesta`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dataToSend),
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || `Error HTTP: ${response.status}`);
        }

        console.log('Respuesta del servidor:', result);
        localStorage.removeItem('surveyDataTemp'); // Limpiar datos temporales
        window.location.href = agradecimientoPage;

    } catch (error) {
        console.error('Error al enviar la encuesta:', error);
        alert(`Error al enviar tu opinión: ${error.message}. Por favor, inténtalo de nuevo.`);
    }
}


    // --- Lógica para la página de agradecimiento.php ---
    if (window.location.pathname.endsWith(agradecimientoPage) || window.location.pathname.endsWith(agradecimientoPage + "/")) {
        setTimeout(() => {
            window.location.href = 'index.php'; // Redirigir a la página inicial
        }, 5000);
    }
});
