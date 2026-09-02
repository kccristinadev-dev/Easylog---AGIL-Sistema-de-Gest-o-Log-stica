CREATE TABLE IF NOT EXISTS entregas (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    pedido_id VARCHAR(80) NULL,
    cliente VARCHAR(255) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    data_prevista DATE NULL,
    status ENUM('Pendente', 'Em separação', 'Em transporte', 'Entregue', 'Cancelada') NOT NULL DEFAULT 'Pendente',
    produtos_relacionados JSON NULL,
    quantidade INT NOT NULL DEFAULT 0,
    observacoes TEXT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_usuario_id (usuario_id),
    KEY idx_status (status),
    CONSTRAINT fk_entregas_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT NOT NULL AUTO_INCREMENT,
    id_produto INT NOT NULL,
    id_usuario INT NOT NULL,
    quantidade INT NOT NULL,
    data DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pendente', 'Em preparação', 'Em transporte', 'Entregue', 'Cancelada') NOT NULL DEFAULT 'Pendente',
    codigo_pedido VARCHAR(80) NULL,
    endereco_entrega VARCHAR(255) NULL,
    telefone VARCHAR(30) NULL,
    observacoes_entrega TEXT NULL,
    PRIMARY KEY (id),
    KEY idx_pedidos_produto (id_produto),
    KEY idx_pedidos_usuario (id_usuario),
    KEY idx_pedidos_codigo (codigo_pedido),
    CONSTRAINT fk_pedidos_produto FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exemplo de consulta para listar entregas por usuário e sugerir rota:
-- SELECT id, cliente, endereco, status, data_prevista, latitude, longitude
-- FROM entregas
-- WHERE usuario_id = ? AND status NOT IN ('Entregue', 'Cancelada')
-- ORDER BY data_prevista ASC, endereco ASC;
