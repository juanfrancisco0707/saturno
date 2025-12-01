// backend-node/db.js
const mysql = require('mysql2/promise');

// Configuración del pool de conexiones con mysql2
const pool = mysql.createPool({
  connectionLimit: 10,
  host: 'localhost',
  user: 'root',
  password: 'clave',
  database: 'bdsaturno'
});

console.log('Pool de conexiones creado.');

// Opcional: Probar la conexión
pool.getConnection()
    .then(connection => {
        console.log('Conexión a la base de datos establecida con éxito.');
        connection.release();
    })
    .catch(err => {
        console.error('Error al conectar con la base de datos:', err);
    });

module.exports = pool;
