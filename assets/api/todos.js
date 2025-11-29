const headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
};

async function handleResponse(res) {
  const contentType = res.headers.get('content-type') || '';
  const isJson = contentType.includes('application/json') || contentType.includes('application/problem+json');
  const body = isJson ? await res.json() : await res.text();
  if (!res.ok) {
    const err = new Error('Request failed');
    err.status = res.status;
    err.body = body;
    throw err;
  }
  return body;
}

export async function listTodos() {
  const res = await fetch('/todos', { headers: { 'Accept': 'application/json' } });
  return handleResponse(res);
}

export async function createTodo(title) {
  const res = await fetch('/todos', {
    method: 'POST',
    headers,
    body: JSON.stringify({ title })
  });
  return handleResponse(res);
}

export async function updateTodo(id, patch) {
  const res = await fetch(`/todos/${encodeURIComponent(id)}`, {
    method: 'PATCH',
    headers,
    body: JSON.stringify(patch)
  });
  return handleResponse(res);
}

export async function listComments(todoId) {
  const res = await fetch(`/todos/${encodeURIComponent(todoId)}/comments`, {
    headers: { 'Accept': 'application/json' }
  });
  return handleResponse(res);
}

export async function createComment(todoId, message) {
  const res = await fetch(`/todos/${encodeURIComponent(todoId)}/comments`, {
    method: 'POST',
    headers,
    body: JSON.stringify({ message })
  });
  return handleResponse(res);
}
