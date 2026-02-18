import React from 'react'
import ReactDOM from 'react-dom/client'
import TodoList from './TodoList'

function App() {
    const tasks = window.laravelTasks || []

    if (tasks.length === 0) {
        return (
            <div className="py-24 text-center text-gray-400 font-black text-xs uppercase tracking-widest">
                No tasks found.
            </div>
        )
    }

    const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const handleAction = (e, url, method = 'POST', confirmMsg = null) => {
        e.preventDefault();
        if (confirmMsg && !confirm(confirmMsg)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = getCsrfToken();
        form.appendChild(csrfInput);

        if (method !== 'POST') {
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = method;
            form.appendChild(methodInput);
        }

        document.body.appendChild(form);
        form.submit();
    };

    return (
        <div className="overflow-x-auto bg-white rounded-[2rem] shadow-sm border border-gray-100">
            <style>{`
                @keyframes shimmer {
                    0% { background-position: 0 0; }
                    100% { background-position: 30px 0; }
                }
                .progress-stripe-animation {
                    background-image: linear-gradient(
                        45deg, 
                        rgba(255, 255, 255, 0.15) 25%, 
                        transparent 25%, 
                        transparent 50%, 
                        rgba(255, 255, 255, 0.15) 50%, 
                        rgba(255, 255, 255, 0.15) 75%, 
                        transparent 75%, 
                        transparent
                    );
                    background-size: 30px 30px;
                    animation: shimmer 2s linear infinite;
                }
            `}</style>

            <table className="w-full min-w-[900px] text-left border-collapse">
                <thead className="bg-gray-50/80 border-b border-gray-100">
                    <tr className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                        <th className="px-8 py-5">Task Details</th>
                        <th className="px-6 py-5 text-center">Priority</th>
                        <th className="px-6 py-5 text-center">Category</th>
                        <th className="px-6 py-5 text-center">Deadline</th>
                        <th className="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-50">
                    {tasks.map(task => {
                        const total = task.subtasks.length
                        const done = task.subtasks.filter(s => s.completed).length
                        const percent = total === 0 ? 0 : Math.round((done / total) * 100)
                        const isFull = percent === 100

                        let displayPriority = task.priority;
                        let displayColor = task.priority_color || '#e5e7eb';

                        if (task.priority_mode === 'auto') {
                            const days = task.days_remaining;
                            if (days < 2) {
                                displayPriority = 'high';
                                displayColor = '#ef4444'; 
                            } else if (days < 5) {
                                displayPriority = 'medium';
                                displayColor = '#f59e0b'; 
                            } else if (days < 10) {
                                displayPriority = 'low';
                                displayColor = '#10b981'; 
                            } else {
                                displayPriority = 'none';
                                displayColor = '#9ca3af'; 
                            }
                        }

                        return (
                            <tr key={task.id} className="hover:bg-gray-50/50 transition-all">
                                <td className="px-8 py-6 align-top">
                                    <div className="flex items-start">
                                        <div 
                                            className="w-1.5 h-10 rounded-full mr-5 shrink-0 mt-1" 
                                            style={{ backgroundColor: displayColor }}
                                        ></div>
                                        
                                        <div className="flex-1">
                                            <div className="flex items-center gap-3 mb-1">
                                                <p className="font-black text-gray-800 text-sm tracking-tight">{task.title}</p>
                                                {task.link && (
                                                    <a 
                                                        href={task.link} 
                                                        target="_blank" 
                                                        rel="noopener noreferrer"
                                                        className="flex items-center gap-1 px-2 py-0.5 bg-gray-100 hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 rounded-md transition-colors group"
                                                        title="Open Attachment Link"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="3">
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                        </svg>
                                                        <span className="text-[8px] font-black uppercase tracking-tighter">Link</span>
                                                    </a>
                                                )}
                                            </div>
                                            <p className="text-[11px] font-bold text-gray-400 mb-4 uppercase tracking-wide">{task.description || 'No description provided'}</p>
                                            
                                            <div className="max-w-[200px] mb-4">
                                                <div className="flex justify-between mb-1.5 text-[9px] font-black uppercase tracking-widest">
                                                    <span className={isFull ? "text-emerald-500" : "text-indigo-500"}>
                                                        {isFull ? 'Completed' : 'Task Progress'}
                                                    </span>
                                                    <span className="text-gray-400 font-bold">{percent}%</span>
                                                </div>
                                                <div className="w-full h-2 bg-gray-100 rounded-full overflow-hidden relative">
                                                    <div 
                                                        className={`h-full transition-all duration-1000 ease-in-out absolute left-0 top-0 ${isFull ? 'bg-emerald-500' : 'bg-indigo-500'} ${percent > 0 ? 'progress-stripe-animation' : ''}`}
                                                        style={{ width: `${percent}%` }}
                                                    ></div>
                                                </div>
                                            </div>

                                            <TodoList subtasks={task.subtasks} />
                                        </div>
                                    </div>
                                </td>
                                <td className="px-6 py-6 text-center align-top">
                                    <div className="flex flex-col items-center gap-1.5">
                                        <span 
                                            className="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-white border shadow-sm"
                                            style={{ color: displayColor, borderColor: displayColor + '40' }}
                                        >
                                            {displayPriority}
                                        </span>
                                        <span className="text-[7px] font-black text-gray-300 uppercase tracking-tighter">
                                            {task.priority_mode}
                                        </span>
                                    </div>
                                </td>
                                <td className="px-6 py-6 text-center align-top">
                                    <span className="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                        {task.category}
                                    </span>
                                </td>
                                <td className="px-6 py-6 text-center align-top">
                                    <span className={`text-[10px] font-black uppercase tracking-widest ${task.is_past ? 'text-red-400' : 'text-gray-500'}`}>
                                        {task.deadline}
                                    </span>
                                </td>
                                <td className="px-8 py-6 text-right align-top">
                                    <div className="flex justify-end items-center gap-1">
                                        <button onClick={(e) => handleAction(e, task.done_url)} className="p-2 text-emerald-500 hover:bg-emerald-50 rounded-xl transition-all active:scale-90" title="Complete">
                                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                        <a href={task.edit_url} className="p-2 text-indigo-500 hover:bg-indigo-50 rounded-xl transition-all active:scale-90" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        <button onClick={(e) => handleAction(e, task.delete_url, 'DELETE', 'Move this task to trash?')} className="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-all active:scale-90" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        )
                    })}
                </tbody>
            </table>
        </div>
    )
}

const root = document.getElementById('react-app')
if (root) { ReactDOM.createRoot(root).render(<App />) }