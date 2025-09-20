let config = { refresh: 3000 };

fetch('config.json')
    .then(res => res.json())
    .then(cfg => { config = cfg; })
    .catch(() => console.warn("No se encontró config.json, usando valores por defecto"));

function actualizarDatos() {
    fetch('sync.php')
        .then(res => res.json())
        .then(data => {
            if (data.error) return console.error(data.error);

// Paso actual
const titulo = document.querySelector('.titulo');
const subtitulo = document.querySelector('.subtitulo');

if (titulo && subtitulo) {
    titulo.className = 'titulo ' + data.paso_actual.estado;
    titulo.textContent = data.paso_actual.nombre;
    subtitulo.textContent = data.paso_actual.descripcion;
}

            // Lista pasos
            const lista = document.querySelector('.pasos');
            if (lista) {
                lista.innerHTML = '';
                data.pasos.forEach(p => {
                    const li = document.createElement('li');
                    li.className = p.estado + (p.id == data.paso_actual.id ? ' actual' : '');
                    li.textContent = p.nombre + (p.estado === 'ok' ? ' ✔' : p.estado === 'fail' ? ' ✖' : '');
                    lista.appendChild(li);
                });
            }

            // Imagen paso
            const imgPaso = document.querySelector('.main img, .img-col img');
            if (imgPaso) {
                imgPaso.src = data.paso_actual.imagen;
            }

            // Datos operador y máquina
            const cards = document.querySelectorAll('.col .card, .rightbar .card');
            if (cards.length >= 7) {
                cards[0].innerHTML = `👷 Operador: ${data.operador.nombre} (${data.operador.turno})`;
                cards[1].innerHTML = `⚡ Ciclo actual (paso): ${data.maquina.ciclo_actual}`;
                cards[2].innerHTML = `📊 Ciclo promedio: ${data.maquina.ciclo_promedio}`;
                cards[3].innerHTML = `✅ Piezas buenas: ${data.maquina.piezas_ok}`;
                cards[4].innerHTML = `❌ Piezas con fallas: ${data.maquina.piezas_ng}`;
                cards[5].innerHTML = `📦 Piezas turno: ${data.maquina.piezas_turno}`;
                cards[6].innerHTML = `🔌 PLC: ${data.maquina.estado_plc}`;
            }
        })
        .catch(err => console.error(err));
}

// Refresco automático
setInterval(actualizarDatos, config.refresh);
