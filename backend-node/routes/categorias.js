// backend-node/routes/categorias.js
const express = require('express');
const router = express.Router();
const pool = require('../db'); // Importamos el pool de conexiones

// GET /api/categorias/ (Equivale a listar.php)
router.get('/', async (req, res) => {
    // Aquí iría la lógica para consultar la base de datos y listar categorías
    let connection;
    try {
        connection = await pool.getConnection();
        const [rows] = await connection.query('SELECT * FROM categorias');
        res.json(rows);
    } catch (error) {
        console.error('Error en la consulta de categorias:', error);
        res.status(500).send('Error en el servidor');
    } finally {
        if (connection) connection.release();
    }
});

// POST /api/categorias/ (Equivale a grabar.php)
router.post('/', (req, res) => {
    const nuevaCategoria = req.body;
    // Lógica para guardar en la base de datos
    res.status(201).json({ message: 'Categoría creada', data: nuevaCategoria });
});

// PUT /api/categorias/:id (Equivale a actualizar.php)
router.put('/:id', (req, res) => {
    const { id } = req.params;
    const datosActualizados = req.body;
    // Lógica para actualizar
    res.json({ message: `Categoría con ID ${id} actualizada` });
});

// DELETE /api/categorias/:id (Equivale a eliminar.php)
router.delete('/:id', (req, res) => {
    const { id } = req.params;
    // Lógica para eliminar
    res.json({ message: `Categoría con ID ${id} eliminada` });
});

module.exports = router;
