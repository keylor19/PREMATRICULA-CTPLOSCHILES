<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header-aprobada { background-color: #15803d; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .header-rechazada { background-color: #b91c1c; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .body { background-color: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; }
        .footer { background-color: #f3f4f6; padding: 16px; border-radius: 0 0 8px 8px; text-align: center; font-size: 12px; color: #6b7280; }
        .codigo { background-color: #dbeafe; color: #1e40af; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 18px; display: inline-block; margin: 12px 0; }
        .dato { margin-bottom: 8px; }
        .dato span { font-weight: bold; }
        .nota { background-color: #f3f4f6; border-left: 4px solid #6b7280; padding: 12px; margin-top: 16px; border-radius: 4px; font-size: 13px; }
    </style>
</head>
<body>
    @if ($prematricula->estado === 'aprobada')
        <div class="header-aprobada">
            <h2 style="margin:0">✓ Prematrícula aprobada</h2>
            <p style="margin:4px 0 0">{{ config('app.name') }}</p>
        </div>
    @else
        <div class="header-rechazada">
            <h2 style="margin:0">✕ Prematrícula rechazada</h2>
            <p style="margin:4px 0 0">{{ config('app.name') }}</p>
        </div>
    @endif

    <div class="body">
        <p>Estimado/a <strong>{{ $prematricula->tutor->nombre_completo }}</strong>,</p>

        @if ($prematricula->estado === 'aprobada')
            <p>Nos complace informarle que la solicitud de prematrícula del estudiante <strong>{{ $prematricula->estudiante->nombre }} {{ $prematricula->estudiante->apellido }}</strong> ha sido <strong>aprobada</strong>.</p>
        @else
            <p>Le informamos que la solicitud de prematrícula del estudiante <strong>{{ $prematricula->estudiante->nombre }} {{ $prematricula->estudiante->apellido }}</strong> ha sido <strong>rechazada</strong>.</p>
        @endif

        <div class="codigo">{{ $prematricula->codigo }}</div>

        <div class="dato">📚 <span>Nivel:</span> {{ $prematricula->nivel->nombre ?? '—' }}</div>
        <div class="dato">🏫 <span>Sección:</span> {{ $prematricula->seccion->nombre ?? '—' }}</div>
        <div class="dato">🎨 <span>Grupo de taller:</span> {{ $prematricula->grupo_taller ? 'Grupo ' . $prematricula->grupo_taller : '—' }}</div>
        <div class="dato">📅 <span>Fecha de decisión:</span> {{ $prematricula->fecha_decision?->format('d/m/Y') }}</div>

        @if ($prematricula->nota_admin)
            <div class="nota">
                <strong>Nota de la institución:</strong><br>
                {{ $prematricula->nota_admin }}
            </div>
        @endif
    </div>
    <div class="footer">
        {{ config('app.name') }} — Este es un correo automático, por favor no responda a este mensaje.
    </div>
</body>
</html>