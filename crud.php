<?php
// crud.php

require_once 'config.php';

// Define o cabeçalho para retornar JSON
header('Content-Type: application/json');

// --- Função para listar todas as tarefas ---
function listarTarefas($pdo) {
    $stmt = $pdo->query("SELECT * FROM tarefas ORDER BY concluida ASC, data_criacao DESC");
    $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'tarefas' => $tarefas]);
}

// Verifica a ação solicitada via POST
if (!isset($_POST['action'])) {
    // Se não houver ação, apenas lista as tarefas (usado na carga inicial da página)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        listarTarefas($pdo);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Ação não especificada.']);
    exit;
}

$action = $_POST['action'];

try {
    switch ($action) {
        
        // --- Ação: Adicionar Tarefa ---
        case 'adicionar':
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if (empty($titulo)) {
                throw new Exception('O título da tarefa não pode ser vazio.');
            }

            $stmt = $pdo->prepare("INSERT INTO tarefas (titulo, descricao) VALUES (:titulo, :descricao)");
            $stmt->execute(['titulo' => $titulo, 'descricao' => $descricao]);
            
            echo json_encode(['success' => true, 'message' => 'Tarefa adicionada com sucesso!']);
            break;

        // --- Ação: Excluir Tarefa ---
        case 'excluir':
            $id = (int)$_POST['id'];
            if ($id <= 0) {
                throw new Exception('ID inválido.');
            }

            $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Tarefa excluída com sucesso!']);
            break;

        // --- Ação: Alternar Status (Concluída/Pendente) ---
        case 'alternar_status':
            $id = (int)$_POST['id'];
            $concluida = (int)$_POST['concluida']; // 1 ou 0
            
            if ($id <= 0 || ($concluida != 0 && $concluida != 1)) {
                 throw new Exception('Dados de status inválidos.');
            }

            $stmt = $pdo->prepare("UPDATE tarefas SET concluida = :concluida WHERE id = :id");
            $stmt->execute(['concluida' => $concluida, 'id' => $id]);
            
            $status_msg = $concluida ? 'concluída' : 'marcada como pendente';
            echo json_encode(['success' => true, 'message' => "Tarefa $status_msg com sucesso!"]);
            break;
            
        // --- Ação: Listar Tarefas (para recarregar a lista) ---
        case 'listar':
            listarTarefas($pdo);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Ação desconhecida.']);
            break;
    }
    
} catch (Exception $e) {
    // Retorna erro em formato JSON
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>