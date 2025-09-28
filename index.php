<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tarefas - PHP/PDO/Ajax/AdminLTE</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body {
            background-color: #f4f6f9; /* Cor de fundo padrão do AdminLTE */
        }
        .card-header {
            background-color: #007bff; /* Cor primária do AdminLTE */
            color: white;
        }
        .todo-list .handle {
            cursor: move;
            margin-right: 5px;
        }
        .todo-list li {
            position: relative;
            padding: 10px;
            border-bottom: 1px solid #eee;
            background-color: #fff;
        }
        .todo-list li:last-child {
            border-bottom: none;
        }
        .text-strike {
            text-decoration: line-through;
            color: #adb5bd;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="content-wrapper" style="min-height: 100vh; padding: 20px;">
        <div class="container-fluid">
            
            <h1 class="text-center mb-4">Sistema Simples de Lista de Tarefas</h1>
            
            <div class="row justify-content-center">
                <div class="col-md-8">

                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Adicionar Nova Tarefa</h3>
                        </div>
                        <form id="formAdicionarTarefa" class="card-body">
                            <div class="form-group">
                                <label for="titulo">Título</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                            <div class="form-group">
                                <label for="descricao">Descrição (Opcional)</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success float-right"><i class="fas fa-plus"></i> Adicionar Tarefa</button>
                        </form>
                    </div>
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Minhas Tarefas</h3>
                        </div>
                        <div class="card-body">
                            <ul class="todo-list" id="listaTarefas">
                                </ul>
                        </div>
                        </div>
                    </div>
            </div>
            
        </div>
    </div>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    
    // URL do nosso script PHP para operações CRUD
    const API_URL = 'crud.php';

    // Função para carregar e exibir a lista de tarefas
    function carregarTarefas() {
        $.ajax({
            url: API_URL,
            type: 'GET',
            data: { action: 'listar' }, // Passa a ação 'listar'
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#listaTarefas').empty();
                    if (response.tarefas.length === 0) {
                         $('#listaTarefas').html('<li class="text-center p-3 text-muted">Nenhuma tarefa cadastrada.</li>');
                         return;
                    }
                    
                    $.each(response.tarefas, function(i, tarefa) {
                        const isDone = parseInt(tarefa.concluida) === 1;
                        const concluidaClass = isDone ? 'text-strike' : '';
                        const checkedAttr = isDone ? 'checked' : '';

                        const itemHtml = `
                            <li class="p-2 ${isDone ? 'bg-light' : ''}">
                                <div class="icheck-primary d-inline mr-2">
                                    <input type="checkbox" value="${tarefa.id}" name="todo" id="todoCheck${tarefa.id}" ${checkedAttr} data-id="${tarefa.id}">
                                    <label for="todoCheck${tarefa.id}"></label>
                                </div>
                                <span class="text ${concluidaClass}" style="font-weight: 600;">${tarefa.titulo}</span>
                                <small class="badge badge-info ml-2">${isDone ? 'Concluída' : 'Pendente'}</small>
                                <p class="mb-0 text-muted ml-4">${tarefa.descricao || 'Sem descrição.'}</p>
                                <div class="tools float-right">
                                    <button class="btn btn-sm btn-danger excluir-tarefa" data-id="${tarefa.id}" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </li>
                        `;
                        $('#listaTarefas').append(itemHtml);
                    });
                } else {
                    alert('Erro ao carregar tarefas: ' + response.message);
                }
            },
            error: function() {
                alert('Erro na comunicação com o servidor ao carregar tarefas.');
            }
        });
    }

    // Chama a função para carregar a lista ao iniciar
    carregarTarefas();

    // --- Tratamento do Formulário de Adicionar Tarefa (Ajax) ---
    $('#formAdicionarTarefa').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adicionando...');

        $.ajax({
            url: API_URL,
            type: 'POST',
            data: form.serialize() + '&action=adicionar',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    form.trigger('reset'); // Limpa o formulário
                    carregarTarefas(); // Recarrega a lista
                } else {
                    alert('Falha ao adicionar: ' + response.message);
                }
            },
            error: function() {
                alert('Erro na comunicação com o servidor.');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // --- Alternar Status da Tarefa (Ajax) ---
    $('#listaTarefas').on('change', 'input[type="checkbox"]', function() {
        const id = $(this).data('id');
        const concluida = this.checked ? 1 : 0;
        
        $.ajax({
            url: API_URL,
            type: 'POST',
            data: { 
                action: 'alternar_status', 
                id: id, 
                concluida: concluida 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Atualiza apenas o item da lista visualmente
                    carregarTarefas(); // Recarrega a lista para ordenar e aplicar classes
                } else {
                    alert('Falha ao alterar status: ' + response.message);
                    this.checked = !this.checked; // Reverte o checkbox em caso de erro
                }
            },
            error: function() {
                alert('Erro na comunicação com o servidor.');
                this.checked = !this.checked; // Reverte o checkbox
            }
        });
    });

    // --- Excluir Tarefa (Ajax) ---
    $('#listaTarefas').on('click', '.excluir-tarefa', function() {
        const id = $(this).data('id');
        
        if (confirm('Tem certeza que deseja excluir esta tarefa?')) {
            $.ajax({
                url: API_URL,
                type: 'POST',
                data: { action: 'excluir', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        carregarTarefas(); // Recarrega a lista
                    } else {
                        alert('Falha ao excluir: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro na comunicação com o servidor.');
                }
            });
        }
    });

});
</script>

</body>
</html>