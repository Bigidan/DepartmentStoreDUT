import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FilterItemComponent } from "../filter-item/filter-item.component";
import { NgForOf } from "@angular/common";

@Component({
  selector: 'app-filter-group',
  standalone: true,
  imports: [
    FilterItemComponent,
    NgForOf
  ],
  template: `
    <div class="filter-group">
      <app-filter-item
          *ngFor="let item of items"
          [name]="item.name"
          [id]="item.id"
          [count]="item.counts"
          [isActive]="item.isActive"
          (activeChange)="onActiveChange(item, $event)">
      </app-filter-item>
    </div>
  `,
  styleUrl: "filter-group.component.css"
})

export class FilterGroupComponent {
  @Input() items: any[] = [];
  @Input() groupName: string = '';
  @Output() activeItemsChange = new EventEmitter<{group: string, activeItems: Set<string>}>();

  activeItems = new Set<string>();

  onActiveChange(item: any, isActive: boolean) {
    item.isActive = isActive;
    if (isActive) {
      this.activeItems.add(item.id);
    } else {
      this.activeItems.delete(item.id);
    }
    this.activeItemsChange.emit({ group: this.groupName, activeItems: this.activeItems });
  }
}
