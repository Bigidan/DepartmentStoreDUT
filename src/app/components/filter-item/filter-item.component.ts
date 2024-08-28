import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-filter-item',
  standalone: true,
  imports: [],
  template: `
    <a href="javascript:void(0)">
    <div class="filter-item" [class.active]="isActive" (click)="toggleActive()">
      <span>{{ name }}</span>
      <span class="badge">{{ count }}</span>
    </div>
    </a>
  `,
  styleUrl: "filter-item.component.css"
})
export class FilterItemComponent {
  @Input() name: string = '';
  @Input() count: number = 0;
  @Input() isActive: boolean = false;
  @Input() id: number = 0;
  @Output() activeChange = new EventEmitter<boolean>();

  toggleActive() {
    this.isActive = !this.isActive;
    this.activeChange.emit(this.isActive);
  }
}
