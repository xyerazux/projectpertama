import React, { useState, useEffect } from 'react'
import TodoList from './TodoList'

function App() {
  const [todos, setTodos] = useState([
    { id: 1, task: 'Membuat foto copy', completed: false },
    { id: 2, task: 'print', completed: false },
    { id: 3, task: 'keppo', completed: false },
  ])

  const [progress, setProgress] = useState(0)

  useEffect(() => {
    const total = todos.length
    const completedTasks = todos.filter(t => t.completed).length
    const percentage = total === 0 ? 0 : Math.round((completedTasks / total) * 100)
    setProgress(percentage)
  }, [todos])

  const handleToggle = (id) => {
    const updatedTodos = todos.map(todo => 
      todo.id === id ? { ...todo, completed: !todo.completed } : todo
    )
    setTodos(updatedTodos)
  }

  const handleSave = () => {
    alert(`Progress tersimpan: ${progress}%`)
  }

  return (
    <div style={{ backgroundColor: '#f4f7f9', minHeight: '100vh', padding: '40px' }}>
      <div style={{ maxWidth: '450px', margin: '0 auto' }}>
        <div style={{ backgroundColor: 'white', padding: '25px', borderRadius: '15px', marginBottom: '20px', textAlign: 'center' }}>
          <h2 style={{ margin: 0 }}>TO-DO PROGRESS</h2>
          <div style={{ width: '100%', backgroundColor: '#edf2f7', borderRadius: '50px', height: '15px', marginTop: '20px' }}>
            <div style={{ 
              width: `${progress}%`, 
              backgroundColor: '#3182ce', 
              height: '100%', 
              borderRadius: '50px', 
              transition: 'width 0.5s' 
            }} />
          </div>
          <p style={{ fontWeight: 'bold', color: '#3182ce', marginTop: '10px' }}>{progress}% Complete</p>
        </div>

        <TodoList todos={todos} onToggle={handleToggle} onSave={handleSave} />
      </div>
    </div>
  )
}

export default App