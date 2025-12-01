const express = require('express');
const cors = require('cors');
const bcrypt = require('bcrypt');
const pool = require('./db'); // Importar el pool de conexiones centralizado

// Importar los enrutadores
const categoriasRouter = require('./routes/categorias');
const clientesRouter = require('./routes/clientes');
const tecnicosRouter = require('./routes/tecnicos');

const app = express();
app.use(cors());
app.use(express.json());
const port = process.env.PORT || 3000;

app.get('/', (req, res) => {
  res.send('Servidor Node.js funcionando!');
});

// Endpoint de login (lo mantenemos aquí por ahora)
app.post('/login', async (req, res) => {
  const { username, password } = req.body;

  if (!username || !password) {
    return res.status(400).json({ success: false, message: 'Faltan datos' });
  }

  let connection;
  try {
    connection = await pool.getConnection();
    const [rows] = await connection.execute('SELECT id, username, password FROM usuarios WHERE username = ?', [username]);

    if (rows.length === 0) {
      return res.status(404).json({ success: false, message: 'Usuario no encontrado' });
    }

    const user = rows[0];
    
    const hash = user.password.replace(/^\$2y\$/, '$2a$');
    const match = await bcrypt.compare(password, hash);

    if (match) {
      res.json({
        success: true,
        user: {
          id: user.id,
          username: user.username
        }
      });
    } else {
      res.status(401).json({ success: false, message: 'Contraseña incorrecta' });
    }
  } catch (error) {
    console.error('Error en el login:', error);
    res.status(500).json({ success: false, message: 'Error en el servidor' });
  } finally {
    if (connection) {
      connection.release();
    }
  }
});

// API Endpoints
// Usamos los enrutadores con el prefijo /api
app.use('/api/categorias', categoriasRouter);
app.use('/api/clientes', clientesRouter);
app.use('/api/tecnicos', tecnicosRouter);


app.listen(port, () => {
  console.log(`Servidor escuchando en http://localhost:${port}`);
});