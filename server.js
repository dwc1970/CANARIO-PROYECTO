const express = require('express');
const path = require('path');
const sqlite3 = require('sqlite3').verbose();
const bodyParser = require('body-parser');

const app = express();
const PORT = process.env.PORT || 3000;

// --- CONFIGURACIÓN DE BASE DE DATOS ---
// Se crea el archivo en la raíz de la carpeta CANARIO
const db = new sqlite3.Database('./club_constitucion.db', (err) => {
    if (err) console.error("Error al abrir DB:", err.message);
    else console.log("Base de Datos SQLite: CONECTADA");
});

// Crear tabla si no existe (Estructura profesional)
db.run(`CREATE TABLE IF NOT EXISTS socios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    dni TEXT UNIQUE NOT NULL,
    categoria TEXT,
    nro_socio TEXT,
    foto TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
)`);

// --- MIDDLEWARES ---
app.use(bodyParser.json({ limit: '10mb' })); // Para recibir la foto del carnet
app.use(bodyParser.urlencoded({ extended: true, limit: '10mb' }));

// Servir archivos estáticos
app.use(express.static(path.join(__dirname, './')));
app.use('/clubc', express.static(path.join(__dirname, 'clubc')));

// --- RUTAS DEL SERVIDOR ---

// 1. Verificar Socio (Login)
app.post('/verificar-socio', (req, res) => {
    const { dni } = req.body;
    db.get("SELECT * FROM socios WHERE dni = ?", [dni], (err, row) => {
        if (err) return res.status(500).json({ success: false, error: err.message });
        if (row) {
            res.json({ success: true, socio: row });
        } else {
            res.json({ success: false, message: "Socio no encontrado" });
        }
    });
});

// 2. Registrar Socio (Desde Index o Admin)
app.post('/registrar-socio', (req, res) => {
    const { nombre, dni, categoria, nro_socio, foto } = req.body;
    
    const sql = `INSERT INTO socios (nombre, dni, categoria, nro_socio, foto) VALUES (?, ?, ?, ?, ?)`;
    const params = [nombre.toUpperCase(), dni, categoria, nro_socio, foto];

    db.run(sql, params, function(err) {
        if (err) {
            if (err.message.includes("UNIQUE")) {
                return res.json({ success: false, message: "Este DNI ya está registrado." });
            }
            return res.status(500).json({ success: false, error: err.message });
        }
        res.json({ success: true, id: this.lastID });
    });
});

// 3. Listar socios (Para el Panel Admin)
app.get('/api/socios', (req, res) => {
    db.all("SELECT id, nombre, dni, categoria, nro_socio, fecha_registro FROM socios ORDER BY id DESC", [], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

// 4. Eliminar Socio (Opcional para el Admin)
app.delete('/api/socios/:id', (req, res) => {
    db.run("DELETE FROM socios WHERE id = ?", req.params.id, (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true });
    });
});

// --- INICIO ---
app.listen(PORT, () => {
    console.log(`
    ==================================================
    SERVIDOR CANARIO ACTIVO
    URL: http://localhost:${PORT}
    ==================================================
    `);
});