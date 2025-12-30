document.addEventListener('DOMContentLoaded', function() {
    // Forzamos una pequeña espera para asegurar que el navegador leyó el disco
    setTimeout(cargarDatosSocio, 100);
    generarMeses();
});

function cargarDatosSocio() {
    // Traemos los datos de la memoria
    const nombre = localStorage.getItem('socioNombre');
    const dni = localStorage.getItem('socioDni');
    const nro = localStorage.getItem('socioNro');
    const cat = localStorage.getItem('socioCat');
    const foto = localStorage.getItem('socioFoto');

    // DEBUG: Esto mostrará en la consola del navegador si los datos existen
    console.log("Datos recuperados:", { nombre, dni, nro, cat });

    if (nombre) {
        document.getElementById('perfil-nombre').textContent = nombre;
        document.getElementById('perfil-dni').textContent = dni;
        document.getElementById('perfil-nro').textContent = nro;
        document.getElementById('perfil-cat').textContent = cat;
        
        if (foto) {
            document.getElementById('perfil-foto').src = foto;
        }
    } else {
        // Si no hay datos, mostramos un aviso
        document.getElementById('perfil-nombre').textContent = "ERROR: REGÍSTRATE DE NUEVO";
    }
}

function generarMeses() {
    // Meses en mayúsculas para que coincidan con tus imágenes
    const meses = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];
    const contenedor = document.getElementById('contenedor-meses');
    if (!contenedor) return;
    
    contenedor.innerHTML = '';
    meses.forEach((mes, index) => {
        // Simulación: los primeros 10 meses están al día, los últimos 2 pendientes
        const pagado = index < 10;
        const card = document.createElement('div');
        card.className = 'mes-card';
        
        // El enlace ahora apunta directamente a pagos.html
        card.innerHTML = `
            <h4>${mes}</h4>
            <span class="status-text ${pagado ? 'pagado' : 'debe'}">${pagado ? 'CUOTA AL DÍA' : 'CUOTA PENDIENTE'}</span>
            <a href="pagos.html" class="btn-cuota ${pagado ? 'btn-pagar-verde' : 'btn-pagar-rojo'}">PAGAR CUOTA</a>
        `;
        contenedor.appendChild(card);
    });
}

function enviarInvitacionWhatsApp() {
    const nombreSocio = localStorage.getItem('socioNombre') || "Un socio";
    // Detecta automáticamente si estás en local o en un servidor web
    const urlClub = window.location.origin + window.location.pathname.replace('perfil.html', 'index.html'); 
    const mensaje = encodeURIComponent(`¡Hola! 👋 *${nombreSocio}* te invita a asociarte al C.S. y D. Constitución (El Canario) 🟡⚫. \n\nSumate aquí: ${urlClub}`);
    window.open(`https://wa.me/?text=${mensaje}`, '_blank');
}