<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1e40af; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .body { background-color: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; }
        .footer { background-color: #f3f4f6; padding: 16px; border-radius: 0 0 8px 8px; text-align: center; font-size: 12px; color: #6b7280; }
        .codigo { background-color: #dbeafe; color: #1e40af; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 18px; display: inline-block; margin: 12px 0; }
        .dato { margin-bottom: 8px; }
        .dato span { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0">Matrícula recibida</h2>
        <p style="margin:4px 0 0">{{ config('app.name') }}</p>
    </div>
    <div class="body">
        <p>Estimado/a <strong>{{ $prematricula->tutor->nombre_completo }}</strong>,</p>
        <p>Hemos recibido correctamente la matrícula con el siguiente detalle:</p>

        <div class="codigo">{{ $prematricula->codigo }}</div>

        <div class="dato">📋 <span>Estudiante:</span> {{ $prematricula->estudiante->nombre }} {{ $prematricula->estudiante->apellido }}</div>
        <div class="dato">📚 <span>Nivel:</span> {{ $prematricula->nivel->nombre ?? '—' }}</div>
        <div class="dato">🏫 <span>Sección preferida:</span> {{ $prematricula->seccion->nombre ?? 'Sin preferencia' }}</div>
        <div class="dato">🎨 <span>Grupo de taller:</span> {{ $prematricula->grupo_taller ? 'Grupo ' . $prematricula->grupo_taller : '—' }}</div>
    </div>
    <div class="footer">
        {{ config('app.name') }} — Este es un correo automático, por favor no responda a este mensaje.
    </div>
</body>
</html>