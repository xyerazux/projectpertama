import React from 'react'

const TodoList = ({ todos, onToggle, onSave }) => {
  return (
    <div style={{ padding: '20px', backgroundColor: '#ffffff', borderRadius: '12px', boxShadow: '0 4px 10px rgba(0,0,0,0.05)', border: '1px solid #eee' }}>
      <h3 style={{ marginBottom: '15px', color: '#333', fontSize: '18px' }}>Task List</h3>
      <ul style={{ listStyleType: 'none', padding: 0 }}>
        {todos.map((todo) => (
          <li 
            key={todo.id} 
            style={{ 
              display: 'flex', 
              alignItems: 'center', 
              padding: '12px 0', 
              borderBottom: '1px solid #f9f9f9' 
            }}
          >
            <input
              type="checkbox"
              checked={todo.completed}
              onChange={() => onToggle(todo.id)}
              style={{ marginRight: '15px', width: '20px', height: '20px', cursor: 'pointer', accentColor: '#007bff' }}
            />
            <span style={{ 
              textDecoration: todo.completed ? 'line-through' : 'none',
              color: todo.completed ? '#bbb' : '#444',
              fontSize: '16px',
              flexGrow: 1
            }}>
              {todo.task}
            </span>
          </li>
        ))}
      </ul>
      
      <button 
        onClick={onSave}
        style={{
          marginTop: '25px',
          width: '100%',
          padding: '14px',
          backgroundColor: '#007bff',
          color: 'white',
          border: 'none',
          borderRadius: '10px',
          fontSize: '16px',
          fontWeight: '600',
          cursor: 'pointer'
        }}
      >
        Done & Save Progress
      </button>
    </div>
  )
}

export default TodoList