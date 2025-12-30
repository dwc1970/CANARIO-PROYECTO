const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const bodyParser = require('body-parser');
const cors = require('cors');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(cors());
app.use(bodyParser.json());

// Crear carpeta "oculta" en Windows si no existe
const dir = './.datos_secretos';
if (!fs.existsSync(dir)){
    fs.mkdirSync(dir);
}

// Conectar a SQLite
const dbPath = path.join(dir, 'club_constitucion.db');
const db = new sqlite3.Database(dbPath);

// Crear la tabla de socios según tu formulario
db.serialize(() => {
    db.run(`CREATE TABLE IF NOT EXISTS socios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT,
        dni TEXT,
        categoria TEXT,
        nro_socio TEXT,
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
    )`);
});

// 1. RUTA PARA GUARDAR (Desde index.html)
app.post('/guardar-socio', (req, res) => {
    const { nombre, dni, categoria, nro_socio } = req.body;
    const query = `INSERT INTO socios (nombre, dni, categoria, nro_socio) VALUES (?, ?, ?, ?)`;
    
    db.run(query, [nombre, dni, categoria, nro_socio], function(err) {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ message: "Socio guardado con éxito", id: this.lastID });
    });
});

// 2. NUEVA RUTA PARA LEER (Desde admin.html)
app.get('/obtener-socios', (req, res) => {
    const query = `SELECT * FROM socios ORDER BY id DESC`;
    
    db.all(query, [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json(rows); // Envía la lista de socios al panel de administración
    });
});

app.listen(3000, () => {
    console.log("✅ Servidor corriendo en http://localhost:3000");
    console.log("📂 Base de datos vinculada en: " + dbPath);
    console.log("🚀 Listo para recibir registros y mostrar el Panel Admin");
});