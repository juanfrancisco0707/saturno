// backend-node/routes/clientes.js
const express = require('express');
const router = express.Router();
const pool = require('../db'); // Importamos el pool de conexiones

// GET /api/clientes/
router.get('/', async (req, res) => {
    let connection;
    try {
        connection = await pool.getConnection();
        const [rows] = await connection.query('SELECT * FROM clientes');
        res.json(rows);
    } catch (error) {
        console.error('Error en la consulta de clientes:', error);
        res.status(500).send('Error en el servidor');
    } finally {
        if (connection) connection.release();
    }
});

// Aquí puedes añadir las otras rutas para clientes (POST, PUT, DELETE)

module.exports = router;
