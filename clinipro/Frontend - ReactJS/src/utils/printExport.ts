import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { Odontogram, ToothCondition } from '@/types';

export interface ExportOptions {
  includeChart?: boolean;
  includeTreatmentPlan?: boolean;
  includeNotes?: boolean;
  includePeriodontalAssessment?: boolean;
  format?: 'pdf' | 'image';
  quality?: number;
}

export class OdontogramExporter {
  
  /**
   * Export odontogram to PDF
   */
  static async exportToPDF(
    odontogram: Odontogram, 
    chartElement?: HTMLElement,
    options: ExportOptions = {}
  ): Promise<void> {
    const {
      includeChart = true,
      includeTreatmentPlan = true,
      includeNotes = true,
      includePeriodontalAssessment = true,
      quality = 1
    } = options;

    try {
      const pdf = new jsPDF('p', 'mm', 'a4');
      let yPosition = 20;

      // Header
      pdf.setFontSize(20);
      pdf.setFont('helvetica', 'bold');
      pdf.text('Dental Chart Report', 20, yPosition);
      yPosition += 10;

      // Patient Information
      pdf.setFontSize(12);
      pdf.setFont('helvetica', 'normal');
      
      const patientName = odontogram.patient_id?.full_name || 
                         `${odontogram.patient_id?.first_name || ''} ${odontogram.patient_id?.last_name || ''}`.trim() || 
                         'Unknown Patient';
      const patientAge = odontogram.patient_id?.age ? `${odontogram.patient_id.age} years` : 'Age not specified';
      const doctorName = odontogram.doctor_id?.first_name && odontogram.doctor_id?.last_name
                        ? `Dr. ${odontogram.doctor_id.first_name} ${odontogram.doctor_id.last_name}`
                        : 'Doctor not specified';
      
      pdf.text(`Patient: ${patientName}`, 20, yPosition);
      yPosition += 6;
      pdf.text(`Age: ${patientAge}`, 20, yPosition);
      yPosition += 6;
      pdf.text(`Examination Date: ${new Date(odontogram.examination_date).toLocaleDateString()}`, 20, yPosition);
      yPosition += 6;
      pdf.text(`Doctor: ${doctorName}`, 20, yPosition);
      yPosition += 6;
      pdf.text(`Numbering System: ${odontogram.numbering_system?.toUpperCase() || 'UNIVERSAL'}`, 20, yPosition);
      yPosition += 10;

      // Dental Chart Image
      if (includeChart && chartElement) {
        try {
          const canvas = await html2canvas(chartElement, {
            scale: quality,
            useCORS: true,
            backgroundColor: '#ffffff',
            allowTaint: true,
            logging: false,
            scrollX: 0,
            scrollY: 0
          });
          
          const imgData = canvas.toDataURL('image/png');
          const imgWidth = 170;
          const imgHeight = (canvas.height * imgWidth) / canvas.width;
          
          // Check if we need a new page
          if (yPosition + imgHeight > 280) {
            pdf.addPage();
            yPosition = 20;
          }
          
          pdf.addImage(imgData, 'PNG', 20, yPosition, imgWidth, imgHeight);
          yPosition += imgHeight + 10;
        } catch (error) {
          console.error('Error capturing chart:', error);
          pdf.text('Chart image could not be generated', 20, yPosition);
          yPosition += 10;
        }
      }

      // Treatment Summary
      if (includeTreatmentPlan && odontogram.treatment_summary) {
        // Check if we need a new page
        if (yPosition > 220) {
          pdf.addPage();
          yPosition = 20;
        }

        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('Treatment Summary', 20, yPosition);
        yPosition += 8;

        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        
        const summary = odontogram.treatment_summary;
        pdf.text(`Total Planned Treatments: ${summary.total_planned_treatments || 0}`, 20, yPosition);
        yPosition += 5;
        pdf.text(`Completed Treatments: ${summary.completed_treatments || 0}`, 20, yPosition);
        yPosition += 5;
        pdf.text(`In Progress Treatments: ${summary.in_progress_treatments || 0}`, 20, yPosition);
        yPosition += 5;
        
        const progress = odontogram.treatment_progress || 0;
        pdf.text(`Treatment Progress: ${progress}%`, 20, yPosition);
        yPosition += 10;
      }

      // Tooth Conditions
      if (odontogram.teeth_conditions && odontogram.teeth_conditions.length > 0) {
        // Check if we need a new page
        if (yPosition > 200) {
          pdf.addPage();
          yPosition = 20;
        }

        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('Tooth Conditions', 20, yPosition);
        yPosition += 8;

        pdf.setFontSize(9);
        pdf.setFont('helvetica', 'normal');

        odontogram.teeth_conditions.forEach((tooth: ToothCondition) => {
          if (yPosition > 270) {
            pdf.addPage();
            yPosition = 20;
          }

          pdf.text(`Tooth ${tooth.tooth_number}: ${tooth.overall_condition.replace('_', ' ').toUpperCase()}`, 20, yPosition);
          yPosition += 4;

          if (tooth.surfaces && tooth.surfaces.length > 0) {
            const surfaceText = tooth.surfaces
              .map(s => `${s.surface}: ${s.condition}`)
              .join(', ');
            pdf.text(`  Surfaces: ${surfaceText}`, 25, yPosition);
            yPosition += 4;
          }

          if (tooth.mobility && tooth.mobility > 0) {
            pdf.text(`  Mobility: Grade ${tooth.mobility}`, 25, yPosition);
            yPosition += 4;
          }

          if (tooth.treatment_plan && tooth.treatment_plan.procedure) {
            pdf.text(`  Treatment: ${tooth.treatment_plan.procedure}`, 25, yPosition);
            yPosition += 4;
          }

          if (tooth.notes) {
            const maxWidth = 170;
            const lines = pdf.splitTextToSize(`  Notes: ${tooth.notes}`, maxWidth);
            pdf.text(lines, 25, yPosition);
            yPosition += lines.length * 4;
          }

          yPosition += 2;
        });
      }

      // Periodontal Assessment
      if (includePeriodontalAssessment && odontogram.periodontal_assessment) {
        // Check if we need a new page
        if (yPosition > 220) {
          pdf.addPage();
          yPosition = 20;
        }

        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('Periodontal Assessment', 20, yPosition);
        yPosition += 8;

        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        
        const assessment = odontogram.periodontal_assessment;
        pdf.text(`Bleeding on Probing: ${assessment.bleeding_on_probing ? 'Yes' : 'No'}`, 20, yPosition);
        yPosition += 5;
        pdf.text(`Calculus Present: ${assessment.calculus_present ? 'Yes' : 'No'}`, 20, yPosition);
        yPosition += 5;
        
        if (assessment.plaque_index !== undefined) {
          pdf.text(`Plaque Index: ${assessment.plaque_index}/3`, 20, yPosition);
          yPosition += 5;
        }
        
        if (assessment.gingival_index !== undefined) {
          pdf.text(`Gingival Index: ${assessment.gingival_index}/3`, 20, yPosition);
          yPosition += 5;
        }

        if (assessment.general_notes) {
          yPosition += 3;
          const maxWidth = 170;
          const lines = pdf.splitTextToSize(`Notes: ${assessment.general_notes}`, maxWidth);
          pdf.text(lines, 20, yPosition);
          yPosition += lines.length * 5;
        }
      }

      // General Notes
      if (includeNotes && odontogram.general_notes) {
        // Check if we need a new page
        if (yPosition > 240) {
          pdf.addPage();
          yPosition = 20;
        }

        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('General Notes', 20, yPosition);
        yPosition += 8;

        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        
        const maxWidth = 170;
        const lines = pdf.splitTextToSize(odontogram.general_notes, maxWidth);
        pdf.text(lines, 20, yPosition);
      }

      // Footer
      const pageCount = pdf.getNumberOfPages();
      for (let i = 1; i <= pageCount; i++) {
        pdf.setPage(i);
        pdf.setFontSize(8);
        pdf.setFont('helvetica', 'normal');
        pdf.text(
          `Generated on ${new Date().toLocaleDateString()} - Page ${i} of ${pageCount}`,
          20,
          290
        );
      }

      // Save the PDF
      const filename = `dental-chart-${patientName.replace(/\s+/g, '_')}-${new Date().toISOString().split('T')[0]}.pdf`;
      pdf.save(filename);
      
    } catch (error) {
      console.error('Error generating PDF:', error);
      throw new Error('Failed to generate PDF report');
    }
  }

  /**
   * Export dental chart as image
   */
  static async exportToImage(
    chartElement: HTMLElement,
    patientName: string,
    options: ExportOptions = {}
  ): Promise<void> {
    const { quality = 2 } = options;

    try {
      const canvas = await html2canvas(chartElement, {
        scale: quality,
        useCORS: true,
        backgroundColor: '#ffffff',
        allowTaint: true,
        logging: false,
        scrollX: 0,
        scrollY: 0
      });

      // Create download link
      const link = document.createElement('a');
      const safeName = (patientName || 'Unknown_Patient').replace(/\s+/g, '_');
      link.download = `dental-chart-${safeName}-${new Date().toISOString().split('T')[0]}.png`;
      link.href = canvas.toDataURL('image/png');
      
      // Trigger download
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
    } catch (error) {
      console.error('Error generating image:', error);
      throw new Error('Failed to generate chart image');
    }
  }

  /**
   * Print dental chart
   */
  static async printChart(
    odontogram: Odontogram,
    chartElement?: HTMLElement
  ): Promise<void> {
    try {
      // Create a new window for printing
      const printWindow = window.open('', '_blank');
      if (!printWindow) {
        throw new Error('Popup blocked. Please allow popups for this site.');
      }

      // Generate HTML content for printing
      const printContent = this.generatePrintHTML(odontogram, chartElement);
      
      printWindow.document.write(printContent);
      printWindow.document.close();
      
      // Wait for content to load, then print
      printWindow.onload = () => {
        setTimeout(() => {
          printWindow.print();
          printWindow.close();
        }, 500);
      };
      
    } catch (error) {
      console.error('Error printing chart:', error);
      throw new Error('Failed to print dental chart');
    }
  }

  /**
   * Generate HTML content for printing
   */
  private static generatePrintHTML(
    odontogram: Odontogram,
    chartElement?: HTMLElement
  ): string {
    const chartHTML = chartElement ? chartElement.outerHTML : '<p>Chart not available</p>';
    
    return `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Dental Chart - ${odontogram.patient_id.full_name}</title>
        <style>
          body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            color: #333;
          }
          .header { 
            text-align: center; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
          }
          .patient-info { 
            background: #f5f5f5; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
          }
          .chart-container { 
            text-align: center; 
            margin: 20px 0; 
          }
          .conditions-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
          }
          .conditions-table th, .conditions-table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
          }
          .conditions-table th { 
            background-color: #f2f2f2; 
          }
          .section { 
            margin: 20px 0; 
            page-break-inside: avoid; 
          }
          .section h3 { 
            color: #2563eb; 
            border-bottom: 1px solid #e5e7eb; 
            padding-bottom: 5px; 
          }
          @media print {
            .no-print { display: none; }
            body { margin: 0; }
          }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>Dental Chart Report</h1>
          <p>Generated on ${new Date().toLocaleDateString()}</p>
        </div>
        
        <div class="patient-info">
          <h2>Patient Information</h2>
          <p><strong>Name:</strong> ${odontogram.patient_id.full_name}</p>
          <p><strong>Age:</strong> ${odontogram.patient_id.age}</p>
          <p><strong>Examination Date:</strong> ${new Date(odontogram.examination_date).toLocaleDateString()}</p>
          <p><strong>Doctor:</strong> Dr. ${odontogram.doctor_id.first_name} ${odontogram.doctor_id.last_name}</p>
          <p><strong>Numbering System:</strong> ${odontogram.numbering_system.toUpperCase()}</p>
          <p><strong>Version:</strong> ${odontogram.version}</p>
        </div>

        <div class="section">
          <h3>Dental Chart</h3>
          <div class="chart-container">
            ${chartHTML}
          </div>
        </div>

        ${odontogram.treatment_summary ? `
        <div class="section">
          <h3>Treatment Summary</h3>
          <p><strong>Total Planned:</strong> ${odontogram.treatment_summary.total_planned_treatments || 0}</p>
          <p><strong>Completed:</strong> ${odontogram.treatment_summary.completed_treatments || 0}</p>
          <p><strong>In Progress:</strong> ${odontogram.treatment_summary.in_progress_treatments || 0}</p>
          <p><strong>Progress:</strong> ${odontogram.treatment_progress || 0}%</p>
        </div>
        ` : ''}

        ${odontogram.teeth_conditions && odontogram.teeth_conditions.length > 0 ? `
        <div class="section">
          <h3>Tooth Conditions</h3>
          <table class="conditions-table">
            <thead>
              <tr>
                <th>Tooth</th>
                <th>Condition</th>
                <th>Surfaces</th>
                <th>Mobility</th>
                <th>Treatment</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              ${odontogram.teeth_conditions.map(tooth => `
                <tr>
                  <td>${tooth.tooth_number}</td>
                  <td>${tooth.overall_condition.replace('_', ' ')}</td>
                  <td>${tooth.surfaces?.map(s => `${s.surface}: ${s.condition}`).join(', ') || 'None'}</td>
                  <td>${tooth.mobility ? `Grade ${tooth.mobility}` : 'Normal'}</td>
                  <td>${tooth.treatment_plan?.procedure || 'None'}</td>
                  <td>${tooth.notes || 'None'}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
        ` : ''}

        ${odontogram.periodontal_assessment ? `
        <div class="section">
          <h3>Periodontal Assessment</h3>
          <p><strong>Bleeding on Probing:</strong> ${odontogram.periodontal_assessment.bleeding_on_probing ? 'Yes' : 'No'}</p>
          <p><strong>Calculus Present:</strong> ${odontogram.periodontal_assessment.calculus_present ? 'Yes' : 'No'}</p>
          ${odontogram.periodontal_assessment.plaque_index !== undefined ? 
            `<p><strong>Plaque Index:</strong> ${odontogram.periodontal_assessment.plaque_index}/3</p>` : ''}
          ${odontogram.periodontal_assessment.gingival_index !== undefined ? 
            `<p><strong>Gingival Index:</strong> ${odontogram.periodontal_assessment.gingival_index}/3</p>` : ''}
          ${odontogram.periodontal_assessment.general_notes ? 
            `<p><strong>Notes:</strong> ${odontogram.periodontal_assessment.general_notes}</p>` : ''}
        </div>
        ` : ''}

        ${odontogram.general_notes ? `
        <div class="section">
          <h3>General Notes</h3>
          <p>${odontogram.general_notes}</p>
        </div>
        ` : ''}
      </body>
      </html>
    `;
  }
}

export default OdontogramExporter;
