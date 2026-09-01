# 🦷 Anatomía Dental - SVG Resources

## 📁 Estructura de Carpetas

```
/public/images/teeth/
├── adult/          → Dientes permanentes (32 dientes)
├── child/          → Dientes temporales (20 dientes)
└── README.md       → Este archivo
```

---

## 📋 Listado de Dientes Requeridos

### 🔹 **ADULT (Dentición Permanente - 32 dientes)**

**Cuadrante 1 (Superior Derecho):**
- `18.svg` - Tercer Molar
- `17.svg` - Segundo Molar
- `16.svg` - Primer Molar
- `15.svg` - Segundo Premolar
- `14.svg` - Primer Premolar
- `13.svg` - Canino
- `12.svg` - Incisivo Lateral
- `11.svg` - Incisivo Central

**Cuadrante 2 (Superior Izquierdo):**
- `21.svg` - Incisivo Central
- `22.svg` - Incisivo Lateral
- `23.svg` - Canino
- `24.svg` - Primer Premolar
- `25.svg` - Segundo Premolar
- `26.svg` - Primer Molar
- `27.svg` - Segundo Molar
- `28.svg` - Tercer Molar

**Cuadrante 3 (Inferior Izquierdo):**
- `31.svg` - Incisivo Central
- `32.svg` - Incisivo Lateral
- `33.svg` - Canino
- `34.svg` - Primer Premolar
- `35.svg` - Segundo Premolar
- `36.svg` - Primer Molar
- `37.svg` - Segundo Molar
- `38.svg` - Tercer Molar

**Cuadrante 4 (Inferior Derecho):**
- `41.svg` - Incisivo Central
- `42.svg` - Incisivo Lateral
- `43.svg` - Canino
- `44.svg` - Primer Premolar
- `45.svg` - Segundo Premolar
- `46.svg` - Primer Molar
- `47.svg` - Segundo Molar
- `48.svg` - Tercer Molar

---

### 🔹 **CHILD (Dentición Temporal - 20 dientes)**

**Cuadrante 5 (Superior Derecho Temporal):**
- `55.svg` - Segundo Molar Temporal
- `54.svg` - Primer Molar Temporal
- `53.svg` - Canino Temporal
- `52.svg` - Incisivo Lateral Temporal
- `51.svg` - Incisivo Central Temporal

**Cuadrante 6 (Superior Izquierdo Temporal):**
- `61.svg` - Incisivo Central Temporal
- `62.svg` - Incisivo Lateral Temporal
- `63.svg` - Canino Temporal
- `64.svg` - Primer Molar Temporal
- `65.svg` - Segundo Molar Temporal

**Cuadrante 7 (Inferior Izquierdo Temporal):**
- `71.svg` - Incisivo Central Temporal
- `72.svg` - Incisivo Lateral Temporal
- `73.svg` - Canino Temporal
- `74.svg` - Primer Molar Temporal
- `75.svg` - Segundo Molar Temporal

**Cuadrante 8 (Inferior Derecho Temporal):**
- `81.svg` - Incisivo Central Temporal
- `82.svg` - Incisivo Lateral Temporal
- `83.svg` - Canino Temporal
- `84.svg` - Primer Molar Temporal
- `85.svg` - Segundo Molar Temporal

---

## 🎨 Especificaciones Técnicas para los SVG

### **Dimensiones:**
- **ViewBox:** `0 0 45 55`
- **Aspect Ratio:** Mantener proporciones del diente real

### **Colores:**
- **Fill:** `#e0e0e0` (gris claro) con `opacity: 0.4`
- **Stroke:** `#bdbdbd` (gris medio) con `stroke-width: 1`
- **Background:** Transparente

### **Características:**
- Anatomía simplificada (solo corona visible)
- Sin detalles complejos (raíces, cámaras pulpares)
- Formas limpias y vectoriales
- Optimizado para web (tamaño < 2KB por archivo)

### **Diferenciación por Tipo:**
- **Incisivos:** Forma rectangular/trapezoidal
- **Caninos:** Forma triangular/puntiaguda
- **Premolares:** Forma ovalada con 1-2 cúspides
- **Molares:** Forma rectangular con múltiples cúspides

---

## 📝 Ejemplo de Plantilla SVG

```svg
<svg viewBox="0 0 45 55" xmlns="http://www.w3.org/2000/svg">
  <!-- Molar (ejemplo diente 18) -->
  <path d="M12,20 Q8,25 8,35 Q8,45 12,50 L33,50 Q37,45 37,35 Q37,25 33,20 Z" 
        fill="#e0e0e0" 
        opacity="0.4" 
        stroke="#bdbdbd" 
        stroke-width="1"/>
</svg>
```

---

## ✅ Checklist de Validación

Antes de integrar los SVG, verificar:
- [ ] Nombre de archivo coincide con número FDI (ej: `18.svg`)
- [ ] ViewBox es `0 0 45 55`
- [ ] Colores son neutros (grises)
- [ ] Fondo es transparente
- [ ] Tamaño de archivo < 3KB
- [ ] Se visualiza correctamente en navegador

---

## 🚀 Integración

Una vez colocados los SVG en las carpetas correspondientes:
1. Los archivos serán detectados automáticamente por el sistema
2. Se renderizarán como `background-image` en cada `.interactive-tooth`
3. Las superficies clickeables permanecerán sobre el SVG
4. El sistema seleccionará automáticamente el SVG correcto según el número de diente

---

**Última actualización:** 2025-10-01
**Sistema de Numeración:** FDI (Fédération Dentaire Internationale)



