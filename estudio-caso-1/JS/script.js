
let estudiantes = [
    { nombre: "Santiago", apellidos: "Ramirez Madrigal", nota: 90 },
    { nombre: "Jose", apellidos: "Alvarado Lopez", nota: 50 },
    { nombre: "Sofia", apellidos: "Rojas Castro", nota: 70 }
];

const formulario = document.getElementById("formulario");
const inputNombre = document.getElementById("nombre");
const inputApellidos = document.getElementById("apellidos");
const inputNota = document.getElementById("nota");
const mensajeError = document.getElementById("mensaje-error");
const tabla = document.getElementById("tabla-estudiantes");

function mostrarTabla() {
    tabla.innerHTML = "";

    for (let i = 0; i < estudiantes.length; i++) {
        let fila = document.createElement("tr");

        let celdaNombre = document.createElement("td");
        celdaNombre.textContent = estudiantes[i].nombre;

        let celdaApellidos = document.createElement("td");
        celdaApellidos.textContent = estudiantes[i].apellidos;

        let celdaNota = document.createElement("td");
        celdaNota.textContent = estudiantes[i].nota;

        if (estudiantes[i].nota >= 80) {
            celdaNota.classList.add("nota-alta");
        } else if (estudiantes[i].nota < 65) {
            celdaNota.classList.add("nota-baja");
        }

        fila.appendChild(celdaNombre);
        fila.appendChild(celdaApellidos);
        fila.appendChild(celdaNota);
        tabla.appendChild(fila);
    }
}

function mostrarResumen() {
    let mayor = estudiantes[0];
    let menor = estudiantes[0];
    let suma = 0;

    for (let i = 0; i < estudiantes.length; i++) {
        suma = suma + estudiantes[i].nota;

        if (estudiantes[i].nota > mayor.nota) {
            mayor = estudiantes[i];
        }
        if (estudiantes[i].nota < menor.nota) {
            menor = estudiantes[i];
        }
    }

    let promedio = suma / estudiantes.length;

    document.getElementById("mayor-nota").textContent =
        "Mayor nota: " + mayor.nombre + " " + mayor.apellidos + " (" + mayor.nota + ")";
    document.getElementById("promedio").textContent =
        "Promedio: " + promedio.toFixed(2);
    document.getElementById("menor-nota").textContent =
        "Nota mas baja: " + menor.nota;
}

formulario.addEventListener("submit", function (evento) {
    evento.preventDefault();

    let nombre = inputNombre.value.trim();
    let apellidos = inputApellidos.value.trim();
    let nota = inputNota.value.trim();

    if (nombre === "" || apellidos === "" || nota === "") {
        mensajeError.textContent = "Todos los campos son obligatorios.";
        return;
    }

    if (Number(nota) < 0 || Number(nota) > 100) {
        mensajeError.textContent = "La nota debe estar entre 0 y 100.";
        return;
    }

    mensajeError.textContent = "";

    estudiantes.push({
        nombre: nombre,
        apellidos: apellidos,
        nota: Number(nota)
    });

    mostrarTabla();
    mostrarResumen();

    inputNombre.value = "";
    inputApellidos.value = "";
    inputNota.value = "";
});

mostrarTabla();
mostrarResumen();
