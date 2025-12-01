// backend-node/routes/tecnicos.js
const express = require('express');
const router = express.Router();
const pool = require('../db'); // Importamos el pool de conexiones

// GET /api/tecnicos/
router.get('/', async (req, res) => {
    let connection;
    try {
        connection = await pool.getConnection();
        const [rows] = await connection.query('SELECT * FROM tecnicos');
        res.json(rows);
    } catch (error) {
        console.error('Error en la consulta de tecnicos:', error);
        res.status(500).send('Error en el servidor');
    } finally {
        if (connection) connection.release();
    }
});

// Aquí puedes añadir las otras rutas para tecnicos (POST, PUT, DELETE)

module.exports = router;
