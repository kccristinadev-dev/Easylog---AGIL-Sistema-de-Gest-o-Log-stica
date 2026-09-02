USE sistema;

-- Execute uma vez em bancos que ja possuem a tabela pedidos original.
ALTER TABLE pedidos
    ADD COLUMN codigo_pedido VARCHAR(80) NULL AFTER status,
    ADD COLUMN endereco_entrega VARCHAR(255) NULL AFTER codigo_pedido,
    ADD COLUMN telefone VARCHAR(30) NULL AFTER endereco_entrega,
    ADD COLUMN observacoes_entrega TEXT NULL AFTER telefone,
    ADD KEY idx_pedidos_codigo (codigo_pedido);
