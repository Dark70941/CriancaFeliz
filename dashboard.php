<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$userName = $_SESSION['user_name'] ?? 'Usuário';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Associação Criança Feliz</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #121a1f; margin: 0; padding: 0; }
        .app {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 20px;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .sidebar {
            background: #0e2a33;
            border-radius: 16px;
            padding: 16px 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            color: #fff;
        }
        .sidebar .logo { width: 44px; height: auto; margin-bottom: 6px; }
        .nav-icon {
            width: 44px; height: 44px; border-radius: 10px; display: grid; place-items: center;
            background: #153945; color: #fff; font-weight: 700;
        }
        .nav-icon.active { background: #ff7a00; }
        .content {
            background: #edf1f3;
            border-radius: 16px;
            padding: 20px;
        }
        .topbar { display:flex; justify-content: space-between; align-items:center; }
        .user { display:flex; align-items:center; gap:10px; }
        .user .avatar { width: 44px; height:44px; border-radius:50%; background:#cfd8dc; }
        .grid { display:grid; grid-template-columns: 0.8fr 1fr 260px; gap:20px; margin-top:20px; }
        .card { background:#fff; border-radius:14px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding:16px; }
        .calendar { height: auto; }
        .list { display:flex; flex-direction:column; gap:12px; }
        .pill { padding:10px 12px; border-radius:10px; font-size:14px; }
        .pill.red { background:#ffe5e5; border-left:6px solid #e06b6b; }
        .pill.green { background:#e8f6ea; border-left:6px solid #6fb64f; }
        .stats { display:grid; gap:12px; }
        .stat { background:#fff; border-radius:12px; padding:16px; text-align:center; font-weight:700; }
        .notes-grid { display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:15px; }
        .note { background:#fff; border-radius:12px; padding:14px; display:flex; gap:10px; align-items:flex-start; }
        .badge { width:36px; height:36px; border-radius:10px; display:grid; place-items:center; font-weight:700; color:#fff; }
        .badge.orange { background:#ff7a00; }
        .badge.green { background:#6fb64f; }
        a.logout-btn { background:#e74c3c; color:#fff; padding:10px 16px; border-radius:8px; text-decoration:none; }
        .calendar-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; width: 100%; max-width: 720px; margin: 0 auto; }
        .calendar-header { text-align:center; font-weight:600; padding:8px; color:#666; }
        .calendar-day { 
            aspect-ratio:1; display:flex; align-items:center; justify-content:center; 
            border-radius:8px; cursor:pointer; transition:all 0.2s; font-weight:500;
            background:#f8f9fa; color:#333;
        }
        .calendar-day:hover { background:#e9ecef; }
        .calendar-day.has-notes { background:#ff7a00; color:white; font-weight:700; }
        .calendar-day.today { background:#6fb64f; color:white; font-weight:700; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; }
        .modal-content { 
            position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); 
            background:white; padding:24px; border-radius:12px; width:400px; max-width:90vw;
        }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
        .modal-close { background:none; border:none; font-size:24px; cursor:pointer; }
        .note-textarea { width:100%; height:100px; border:2px solid #f0a36b; border-radius:8px; padding:12px; resize:vertical; }
        .modal-buttons { display:flex; gap:12px; justify-content:flex-end; margin-top:16px; }
        .btn-save { background:#6fb64f; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; }
        .btn-cancel { background:#6c757d; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; }
        .delete-btn { 
            background: #e74c3c; color: white; border: none; width: 20px; height: 20px; 
            border-radius: 50%; cursor: pointer; font-size: 12px; display: flex; 
            align-items: center; justify-content: center; margin-left: auto; 
            transition: all 0.2s; flex-shrink: 0;
        }
        .delete-btn:hover { background: #c0392b; transform: scale(1.1); }
        .note-with-delete { display: flex; align-items: flex-start; gap: 10px; width: 100%; }
        .note-content { flex: 1; }
        
        /* Responsividade */
        @media (max-width: 1200px) {
            .grid { grid-template-columns: 0.9fr 1fr; }
            .stats { grid-column: 1 / -1; grid-template-columns: repeat(3, 1fr); }
            .notes-grid { grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 12px; }
        }
        
        @media (max-width: 1000px) {
            .grid { grid-template-columns: 1fr; }
            .stats { grid-template-columns: repeat(3, 1fr); }
            .notes-grid { grid-template-columns: 1fr 1fr; margin-top: 10px; }
            .calendar { height: auto; }
        }
        
        @media (max-width: 768px) {
            .app { 
                grid-template-columns: 1fr; 
                padding: 10px; 
                gap: 10px; 
            }
            .sidebar { 
                grid-row: 2;
                flex-direction: row; 
                padding: 8px 16px; 
                gap: 12px;
                overflow-x: auto;
            }
            .content { 
                grid-row: 1;
                padding: 15px; 
            }
            .grid { 
                grid-template-columns: 1fr; 
                gap: 15px; 
            }
            .calendar { height: auto; }
            .topbar { 
                flex-direction: column; 
                gap: 10px; 
                align-items: flex-start; 
            }
            .user { 
                align-self: flex-end; 
            }
            .notes-grid { 
                grid-template-columns: 1fr 1fr; 
                margin-top: 10px;
                gap: 15px;
            }
            .stats { 
                grid-template-columns: repeat(3, 1fr); 
                gap: 8px; 
            }
            .stat { padding: 12px; font-size: 14px; }
        }
        
        @media (max-width: 480px) {
            .app { padding: 5px; }
            .content { padding: 10px; }
            .topbar { text-align: center; }
            .user { 
                flex-direction: column; 
                gap: 5px; 
                align-self: center; 
            }
            .calendar { height: auto; }
            .stats { grid-template-columns: 1fr; }
            .notes-grid { 
                grid-template-columns: 1fr; 
                margin-top: 8px;
                gap: 10px; 
            }
            .sidebar { 
                padding: 6px 12px; 
                gap: 8px; 
            }
            .nav-icon { 
                width: 36px; 
                height: 36px; 
                font-size: 14px; 
            }
            .sidebar .logo { width: 36px; }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <img src="img/logo.png" class="logo" alt="logo">
            <div class="nav-icon active">🏠</div>
            <a class="nav-icon" href="prontuarios.php" title="Prontuários">👥</a>
            <div class="nav-icon" title="Relatórios">📈</div>
            <div class="nav-icon" title="Usuários">👤❌</div>
            <div class="nav-icon" title="Permissões">👤🔒</div>
            <div class="nav-icon" title="Ideias">💡</div>
            <div class="nav-icon" title="Documentos">📋</div>
            <div class="nav-icon" title="Editar">📝</div>
            <div class="nav-icon" title="Administrador">🤵</div>
        </aside>
        <main class="content">
            <div class="topbar">
                <div>
                    <div style="font-weight:700">Olá <?php echo htmlspecialchars($userName); ?> - Administrador</div>
                </div>
                <div class="user">
                    <div class="avatar"></div>
                    <div><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
                    <a href="logout.php" class="logout-btn">Sair</a>
                </div>
            </div>
            <section class="grid">
                <div class="card calendar">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <div style="font-weight:700; font-size:18px;" id="currentMonth">Setembro, 2025</div>
                        <div style="display:flex; gap:10px;">
                            <button onclick="changeMonth(-1)" style="background:#ff7a00; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">‹</button>
                            <button onclick="changeMonth(1)" style="background:#ff7a00; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;">›</button>
                        </div>
                    </div>
                    <div class="calendar-grid" id="calendarGrid">
                        <div class="calendar-header">D</div>
                        <div class="calendar-header">S</div>
                        <div class="calendar-header">T</div>
                        <div class="calendar-header">Q</div>
                        <div class="calendar-header">Q</div>
                        <div class="calendar-header">S</div>
                        <div class="calendar-header">S</div>
                    </div>
                </div>
                <div class="card list">
                    <div style="font-weight:700">Alertas Prioritários</div>
                    <div class="pill red">3 assistidos com 4 faltas acumuladas – revisar critérios</div>
                    <div class="pill red">2 assistidos com documentos incompletos</div>
                    <div class="pill green">Oficina de Artes ainda sem voluntário confirmado</div>
                </div>
                <div class="stats">
                    <div class="stat">10<br><span style="font-weight:500">Faltas no mês</span></div>
                    <div class="stat">35<br><span style="font-weight:500">Assistidos ativos</span></div>
                    <div class="stat">5<br><span style="font-weight:500">Desativados</span></div>
                </div>
            </section>
            <section class="notes-grid">
                <div class="card list">
                    <div style="font-weight:700">Anotações do Calendário</div>
                    <div id="notesList">
                        <!-- Anotações serão carregadas dinamicamente -->
                    </div>
                </div>
                <div class="card list">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div style="font-weight:700">Avisos</div>
                        <a href="#" style="font-size:12px; text-decoration:none; color:#333">Ver todos</a>
                    </div>
                    <div class="note">
                        <div class="note-with-delete">
                            <div class="badge green">S</div>
                            <div class="note-content">
                                <div style="font-weight:600">Assistente Social – Ana</div>
                                <div style="font-size:12px;color:#666">Reunião com responsáveis amanhã às 15h.</div>
                            </div>
                            <button class="delete-btn" onclick="deleteAlert(0)" title="Excluir aviso">×</button>
                        </div>
                    </div>
                    <div class="note">
                        <div class="note-with-delete">
                            <div class="badge green">V</div>
                            <div class="note-content">
                                <div style="font-weight:600">Voluntário – Pedro</div>
                                <div style="font-size:12px;color:#666">Levar pincéis e cartolinas para oficina de artes.</div>
                            </div>
                            <button class="delete-btn" onclick="deleteAlert(1)" title="Excluir aviso">×</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal para adicionar/editar anotações -->
    <div id="noteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Adicionar Anotação</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div>
                <label for="noteText">Anotação para <span id="selectedDate"></span>:</label>
                <textarea id="noteText" class="note-textarea" placeholder="Digite sua anotação aqui..."></textarea>
            </div>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
                <button class="btn-save" onclick="saveNote()">Salvar</button>
            </div>
        </div>
    </div>

    <script>
        let currentDate = new Date();
        let notes = JSON.parse(localStorage.getItem('calendarNotes') || '{}');
        let selectedDate = null;

        function generateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();

            const monthNames = [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ];

            document.getElementById('currentMonth').textContent = `${monthNames[month]}, ${year}`;

            const calendarGrid = document.getElementById('calendarGrid');
            // Limpar dias existentes (manter headers)
            const headers = calendarGrid.querySelectorAll('.calendar-header');
            calendarGrid.innerHTML = '';
            headers.forEach(header => calendarGrid.appendChild(header));

            // Adicionar dias vazios no início
            for (let i = 0; i < startingDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day';
                calendarGrid.appendChild(emptyDay);
            }

            // Adicionar dias do mês
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day;
                
                const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                
                // Marcar hoje
                const today = new Date();
                if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                    dayElement.classList.add('today');
                }
                
                // Marcar dias com anotações
                if (notes[dateKey]) {
                    dayElement.classList.add('has-notes');
                }
                
                dayElement.onclick = () => openNoteModal(dateKey, day);
                calendarGrid.appendChild(dayElement);
            }
        }

        function changeMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            generateCalendar();
            updateNotesList();
        }

        function openNoteModal(dateKey, day) {
            selectedDate = dateKey;
            document.getElementById('selectedDate').textContent = `${day}/${currentDate.getMonth() + 1}/${currentDate.getFullYear()}`;
            document.getElementById('noteText').value = notes[dateKey] || '';
            document.getElementById('noteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('noteModal').style.display = 'none';
            selectedDate = null;
        }

        function saveNote() {
            if (selectedDate) {
                const noteText = document.getElementById('noteText').value.trim();
                if (noteText) {
                    notes[selectedDate] = noteText;
                } else {
                    delete notes[selectedDate];
                }
                localStorage.setItem('calendarNotes', JSON.stringify(notes));
                generateCalendar();
                updateNotesList();
                closeModal();
            }
        }

        function updateNotesList() {
            const notesList = document.getElementById('notesList');
            notesList.innerHTML = '';
            
            const currentMonth = currentDate.getMonth();
            const currentYear = currentDate.getFullYear();
            
            const monthNotes = Object.entries(notes).filter(([dateKey, note]) => {
                const [year, month, day] = dateKey.split('-').map(Number);
                return year === currentYear && month === currentMonth + 1;
            }).sort(([a], [b]) => a.localeCompare(b));
            
            if (monthNotes.length === 0) {
                notesList.innerHTML = '<div style="color:#666; font-style:italic;">Nenhuma anotação este mês</div>';
                return;
            }
            
            monthNotes.forEach(([dateKey, note]) => {
                const [year, month, day] = dateKey.split('-').map(Number);
                const noteElement = document.createElement('div');
                noteElement.className = 'note';
                noteElement.innerHTML = `
                    <div class="note-with-delete">
                        <div class="badge orange">${day}</div>
                        <div class="note-content">
                            <div style="font-weight:600">${day}/${month}/${year}</div>
                            <div style="font-size:12px;color:#666">${note}</div>
                        </div>
                        <button class="delete-btn" onclick="deleteNote('${dateKey}')" title="Excluir anotação">×</button>
                    </div>
                `;
                notesList.appendChild(noteElement);
            });
        }

        // Fechar modal clicando fora
        window.onclick = function(event) {
            const modal = document.getElementById('noteModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Função para deletar anotação
        function deleteNote(dateKey) {
            if (confirm('Tem certeza que deseja excluir esta anotação?')) {
                delete notes[dateKey];
                localStorage.setItem('calendarNotes', JSON.stringify(notes));
                generateCalendar();
                updateNotesList();
            }
        }

        // Função para deletar aviso
        let alerts = [
            { badge: 'S', title: 'Assistente Social – Ana', content: 'Reunião com responsáveis amanhã às 15h.' },
            { badge: 'V', title: 'Voluntário – Pedro', content: 'Levar pincéis e cartolinas para oficina de artes.' }
        ];

        function deleteAlert(index) {
            if (confirm('Tem certeza que deseja excluir este aviso?')) {
                alerts.splice(index, 1);
                updateAlertsList();
            }
        }

        function updateAlertsList() {
            const alertsContainer = document.querySelector('.card.list:last-child');
            const alertsContent = alertsContainer.querySelector('div:not(:first-child)');
            
            // Limpar avisos existentes (manter o cabeçalho)
            const existingNotes = alertsContainer.querySelectorAll('.note');
            existingNotes.forEach(note => note.remove());
            
            if (alerts.length === 0) {
                const emptyMessage = document.createElement('div');
                emptyMessage.style.cssText = 'color:#666; font-style:italic; margin-top:10px;';
                emptyMessage.textContent = 'Nenhum aviso disponível';
                alertsContainer.appendChild(emptyMessage);
                return;
            }
            
            alerts.forEach((alert, index) => {
                const noteElement = document.createElement('div');
                noteElement.className = 'note';
                noteElement.innerHTML = `
                    <div class="note-with-delete">
                        <div class="badge green">${alert.badge}</div>
                        <div class="note-content">
                            <div style="font-weight:600">${alert.title}</div>
                            <div style="font-size:12px;color:#666">${alert.content}</div>
                        </div>
                        <button class="delete-btn" onclick="deleteAlert(${index})" title="Excluir aviso">×</button>
                    </div>
                `;
                alertsContainer.appendChild(noteElement);
            });
        }

        // Inicializar calendário
        generateCalendar();
        updateNotesList();
    </script>
    
    <!-- Chatbot -->
    <script src="js/chatbot.js"></script>
    <!-- Modo Escuro -->
    <script src="js/theme-toggle.js"></script>
</body>
</html>
