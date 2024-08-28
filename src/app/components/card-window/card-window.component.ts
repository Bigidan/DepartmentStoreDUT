import {Component, Input} from '@angular/core';
import {NgClass, NgForOf, NgIf} from "@angular/common";
import { HostListener } from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {FormsModule} from "@angular/forms";
import {CardFieldComponent} from "../card-field/card-field.component";
import {DataService} from "../../services/data.service";
import { HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-card-window',
  standalone: true,
  imports: [
    HttpClientModule,
    NgClass,
    NgIf,
    MatIcon,
    NgForOf,
    FormsModule,
    CardFieldComponent
  ],
  templateUrl: "card-window.component.html",
  styleUrls: ['card-window.component.css'],
  providers: [DataService],
})
export class CardWindowComponent {
  @Input() content: string = '';
  constructor(private dataService: DataService) {}
  isVisible = false;


  isFullscreen = true;

  isDragging = false;
  dragStartX = 0;
  dragStartY = 0;
  initialLeft = 0;
  initialTop = 0;
  prevLeft = 300;
  prevTop = 100;

  rows: Row[] = [];
  fields: Field[] = [];

  getData(selectedNavItem: string) {
    this.dataService.getColumnData(selectedNavItem).subscribe(data => {
      this.fields = data.columns;
    });
  }

  // Відкриття картки
  openCard() {
    this.isVisible = true;
  }

  // Закриття картки
  closeCard() {
    this.isVisible = false;
  }

  // Переключення режиму повного екрану
  toggleFullscreen() {
    const cardElement = document.querySelector('.card-window') as HTMLElement;

    if (this.isFullscreen) {
      // Відновлюємо попередню позицію
      cardElement.style.top = `${this.prevTop}px`;
      cardElement.style.left = `${this.prevLeft}px`;
    } else {
      // Зберігаємо попередню позицію
      this.prevTop = cardElement.offsetTop;
      this.prevLeft = cardElement.offsetLeft;
      // Переміщаємо картку в лівий верхній кут
      cardElement.style.top = '100px';
      cardElement.style.left = '300px';
    }

    this.isFullscreen = !this.isFullscreen;
  }

  setContent(content: string) {
    this.content = content;
    console.log(this.content);
    this.getData(this.content);
  }


  onMouseDown(event: MouseEvent) {
    if (this.isFullscreen) return;
    this.isDragging = true;
    this.dragStartX = event.clientX;
    this.dragStartY = event.clientY;
    const cardElement = event.currentTarget as HTMLElement;
    this.initialLeft = cardElement.offsetLeft;
    this.initialTop = cardElement.offsetTop;

    // Start dragging
    document.addEventListener('mousemove', this.onMouseMove);
    document.addEventListener('mouseup', this.onMouseUp);
  }

  onMouseMove = (event: MouseEvent) => {
    if (!this.isDragging) return;
    const dx = event.clientX - this.dragStartX;
    const dy = event.clientY - this.dragStartY;
    const cardElement = document.querySelector('.card-window') as HTMLElement;
    cardElement.style.left = `${this.initialLeft + dx}px`;
    cardElement.style.top = `${this.initialTop + dy}px`;
  }

  onMouseUp = () => {
    this.isDragging = false;
    document.removeEventListener('mousemove', this.onMouseMove);
    document.removeEventListener('mouseup', this.onMouseUp);
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