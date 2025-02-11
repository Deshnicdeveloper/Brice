function generateResultsTable(pupils, subjects, existingResults) {
    let html = `
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pupil</th>
                    ${subjects.map(subject => `
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ${subject.name}<br>
                            <span class="text-gray-400">(Coef: ${subject.coefficient})</span>
                        </th>
                    `).join('')}
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
    `;

    pupils.forEach(pupil => {
        html += `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${pupil.first_name} ${pupil.last_name}</div>
                    <div class="text-sm text-gray-500">${pupil.matricule}</div>
                </td>
        `;

        subjects.forEach(subject => {
            const existingResult = existingResults.find(r => 
                r.pupil_id === pupil.pupil_id && 
                r.subject_id === subject.subject_id
            );

            html += `
                <td class="px-6 py-4">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">1st Sequence</label>
                            <input type="number" step="0.01" min="0" max="20" 
                                   name="marks[${pupil.pupil_id}][${subject.subject_id}][first_sequence]"
                                   value="${existingResult ? existingResult.first_sequence_marks : ''}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                          focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">2nd Sequence</label>
                            <input type="number" step="0.01" min="0" max="20"
                                   name="marks[${pupil.pupil_id}][${subject.subject_id}][second_sequence]"
                                   value="${existingResult ? existingResult.second_sequence_marks : ''}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                          focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Exam</label>
                            <input type="number" step="0.01" min="0" max="20"
                                   name="marks[${pupil.pupil_id}][${subject.subject_id}][exam]"
                                   value="${existingResult ? existingResult.exam_marks : ''}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                          focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Comment</label>
                            <input type="text"
                                   name="marks[${pupil.pupil_id}][${subject.subject_id}][comment]"
                                   value="${existingResult ? existingResult.teacher_comment : ''}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm 
                                          focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>
                </td>
            `;
        });

        html += '</tr>';
    });

    html += `
            </tbody>
        </table>
    `;

    return html;
} 