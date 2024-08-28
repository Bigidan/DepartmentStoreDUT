import {Component, Input} from '@angular/core';
import {FormsModule} from "@angular/forms";
import {NgForOf, NgIf} from "@angular/common";
import {MatFormField} from "@angular/material/form-field";
import {MatOption, MatSelect, MatLabel} from "@angular/material/select";
import {MatIcon} from "@angular/material/icon";
import {DataService} from "../../services/data.service";

@Component({
  selector: 'app-card-field',
  standalone: true,
  imports: [
    FormsModule,
    NgForOf,
    NgIf,
    MatFormField,
    MatSelect,
    MatOption,
    MatLabel,
    MatIcon
  ],
  templateUrl: "card-field.component.html",
  styleUrls: ['card-field.component.css'],
})
export class CardFieldComponent {
  constructor(private dataService: DataService) {}

  rows: Row[] = [];
  @Input() fields: Field[] = [];
  @Input() content: string = '';

  addRaw() {
    // Створення нового рядка
    const newRow: Row = {};
    this.fields.forEach((field) => {
      newRow[field.name] = { ...field };
    });
    this.rows.push(newRow);
  }

  removeRaw(index: number) {
    if (index >= 0 && index < this.rows.length) {
      this.rows.splice(index, 1);
    } else {
      console.error('Invalid index');
    }
  }

  transformRows(rows: any[]): any {
    return rows.map(row => {
      const transformedRow: any = {};
      for (const [key, value] of Object.entries(row)) {
        transformedRow[key] = (value as any).name;
      }
      return transformedRow;
    });
  }

  create(){
    const transformedData = this.transformRows(this.rows);
    this.dataService.sendData(transformedData, this.content).subscribe(
        response => console.log('Дані успішно відправлені', response),
        error => console.error('Помилка при відправці даних', error)
    );
    console.log(transformedData);
  }

}
interface Field {
  name: string;
  type: 'string' | 'dropdown';
  options?: string[];
}

interface Row {
  [key: string]: Field;
}