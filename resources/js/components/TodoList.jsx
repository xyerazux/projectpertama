import React from 'react';

const TodoList = ({ subtasks = [] }) => {
    if (subtasks.length === 0) return null;

    return (
        <div className="mt-2 pl-1">
            <ul className="space-y-2">
                {subtasks.map((sub) => (
                    <li key={sub.id} className="flex items-center gap-3">
                        <div className={`w-1.5 h-1.5 rounded-full shrink-0 ${sub.completed ? 'bg-indigo-400' : 'bg-gray-200'}`}></div>
                        <span className={`text-[11px] font-bold tracking-tight ${sub.completed ? 'text-gray-300 line-through' : 'text-gray-500'}`}>
                            {sub.task}
                        </span>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default TodoList;